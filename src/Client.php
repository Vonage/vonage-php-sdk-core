<?php

declare(strict_types=1);

namespace Vonage;

use Composer\InstalledVersions;
use InvalidArgumentException;
use Laminas\Diactoros\Uri;
use Lcobucci\JWT\Token;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RuntimeException;
use Vonage\Account\ClientFactory;
use Vonage\Application\ClientFactory as ApplicationClientFactory;
use Vonage\Client\APIResource;
use Vonage\Client\APIResourceFactory;
use Vonage\Client\Credentials\Basic;
use Vonage\Client\Credentials\Container;
use Vonage\Client\Credentials\CredentialsInterface;
use Vonage\Client\Credentials\Keypair;
use Vonage\Client\Credentials\SignatureSecret;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Client\Factory\FactoryInterface;
use Vonage\Client\Factory\MapFactory;
use Vonage\Conversion\ClientFactory as ConversionClientFactory;
use Vonage\Insights\ClientFactory as InsightsClientFactory;
use Vonage\Meetings\ClientFactory as MeetingsClientFactory;
use Vonage\Numbers\ClientFactory as NumbersClientFactory;
use Vonage\Redact\ClientFactory as RedactClientFactory;
use Vonage\Secrets\ClientFactory as SecretsClientFactory;
use Vonage\SMS\ClientFactory as SMSClientFactory;
use Vonage\Subaccount\ClientFactory as SubaccountClientFactory;
use Vonage\Messages\ClientFactory as MessagesClientFactory;
use Vonage\Users\ClientFactory as UsersClientFactory;
use Vonage\Verify\ClientFactory as VerifyClientFactory;
use Vonage\Verify2\ClientFactory as Verify2ClientFactory;
use Vonage\Conversation\ClientFactory as ConversationClientFactory;
use Vonage\Voice\ClientFactory as VoiceClientFactory;
use Vonage\Logger\{LoggerAwareInterface, LoggerTrait};

use function array_key_exists;
use function array_merge;
use function call_user_func_array;
use function implode;
use function is_null;
use function method_exists;
use function set_error_handler;
use function str_replace;

/**
 * Vonage API Client, allows access to the API from PHP.
 *
 * @method Account\Client account()
 * @method Messages\Client messages()
 * @method Application\Client applications()
 * @method Conversion\Client conversion()
 * @method Conversation\Client conversation()
 * @method Insights\Client insights()
 * @method Numbers\Client numbers()
 * @method Redact\Client redact()
 * @method Secrets\Client secrets()
 * @method SMS\Client sms()
 * @method Subaccount\Client subaccount()
 * @method Users\Client users()
 * @method Verify\Client  verify()
 * @method Verify2\Client  verify2()
 * @method Voice\Client voice()
 * @method Vonage\Video\Client video()
 *
 * @property string restUrl
 * @property string apiUrl
 */
class Client implements LoggerAwareInterface
{
    use LoggerTrait;

    public const BASE_API = 'https://api.nexmo.com';
    public const BASE_REST = 'https://rest.nexmo.com';

    protected CredentialsInterface $credentials;

    protected ClientInterface $client;

    protected mixed $debug = false;

    protected ContainerInterface $factory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected array $options = ['show_deprecations' => false, 'debug' => false];

    public string $apiUrl;

    public string $restUrl;

    /**
     * Create a new API client using the provided credentials.
     */
    public function __construct(
        CredentialsInterface $credentials,
        $options = [],
        ?ClientInterface $client = null
    ) {
        if (is_null($client)) {
            // Since the user did not pass a client, try and make a client
            // using the Guzzle 6 adapter or Guzzle 7 (depending on availability)
            [$guzzleVersion] = explode('@', (string) InstalledVersions::getVersion('guzzlehttp/guzzle'), 1);
            $guzzleVersion = (float) $guzzleVersion;

            if ($guzzleVersion >= 6.0 && $guzzleVersion < 7) {
                /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                /** @noinspection PhpUndefinedNamespaceInspection */
                /** @noinspection PhpUndefinedClassInspection */
                $client = new \Http\Adapter\Guzzle6\Client();
            }

            if ($guzzleVersion >= 7.0 && $guzzleVersion < 8.0) {
                $client = new \GuzzleHttp\Client();
            }
        }

        $this->setHttpClient($client);

        if (
            !($credentials instanceof Container) &&
            !($credentials instanceof Basic) &&
            !($credentials instanceof SignatureSecret) &&
            !($credentials instanceof Keypair)
        ) {
            throw new RuntimeException('unknown credentials type: ' . $credentials::class);
        }

        $this->credentials = $credentials;

        $this->options = array_merge($this->options, $options);

        // If they've provided an app name, validate it
        if (isset($options['app'])) {
            $this->validateAppOptions($options['app']);
        }

        // Set the default URLs. Keep the constants for
        // backwards compatibility
        $this->apiUrl = static::BASE_API;
        $this->restUrl = static::BASE_REST;

        // If they've provided alternative URLs, use that instead
        // of the defaults
        if (isset($options['base_rest_url'])) {
            $this->restUrl = $options['base_rest_url'];
        }

        if (isset($options['base_api_url'])) {
            $this->apiUrl = $options['base_api_url'];
        }

        if (isset($options['debug'])) {
            $this->debug = $options['debug'];
        }

        $services = [
            // Registered Services by name
            'account' => ClientFactory::class,
            'applications' => ApplicationClientFactory::class,
            'conversion' => ConversionClientFactory::class,
            'conversation' => ConversationClientFactory::class,
            'insights' => InsightsClientFactory::class,
            'numbers' => NumbersClientFactory::class,
            'messages' => MessagesClientFactory::class,
            'redact' => RedactClientFactory::class,
            'secrets' => SecretsClientFactory::class,
            'sms' => SMSClientFactory::class,
            'subaccount' => SubaccountClientFactory::class,
            'users' => UsersClientFactory::class,
            'verify' => VerifyClientFactory::class,
            'verify2' => Verify2ClientFactory::class,
            'voice' => VoiceClientFactory::class,

            // Additional utility classes
            APIResource::class => APIResourceFactory::class,
            Client::class => fn() => $this
        ];

        if (class_exists('Vonage\Video\ClientFactory')) {
            $services['video'] = 'Vonage\Video\ClientFactory';
        } else {
            $services['video'] = function (): never {
                throw new \RuntimeException('Please install @vonage/video to use the Video API');
            };
        }

        $this->setFactory(
            new MapFactory(
                $services,
                $this
            )
        );

        // Disable throwing E_USER_DEPRECATED notices by default, the user can turn it on during development
        if (array_key_exists('show_deprecations', $this->options) && ($this->options['show_deprecations'] == true)) {
            set_error_handler(
                static fn(
                    int $errno,
                    string $errstr,
                    ?string $errfile = null,
                    ?int $errline = null,
                    ?array $errorcontext = null
                ) => true,
                E_USER_DEPRECATED
            );
        }
    }

    public function getRestUrl(): string
    {
        return $this->restUrl;
    }

    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    /**
     * Set the Http Client to used to make API requests.
     *
     * This allows the default http client to be swapped out for a HTTPlug compatible
     * replacement.
     */
    public function setHttpClient(ClientInterface $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get the Http Client used to make API requests.
     */
    public function getHttpClient(): ClientInterface
    {
        return $this->client;
    }

    /**
     * Set the factory used to create API specific clients.
     */
    public function setFactory(FactoryInterface $factory): self
    {
        $this->factory = $factory;

        return $this;
    }

    public function getFactory(): ContainerInterface
    {
        return $this->factory;
    }

    /**
     * @throws ClientException
     */
    public function generateJwt($claims = []): Token
    {
        if (method_exists($this->credentials, "generateJwt")) {
            return $this->credentials->generateJwt($claims);
        }

        throw new ClientException($this->credentials::class . ' does not support JWT generation');
    }

    protected function validateAppOptions($app): void
    {
        $disallowedCharacters = ['/', ' ', "\t", "\n"];

        foreach (['name', 'version'] as $key) {
            if (!isset($app[$key])) {
                throw new InvalidArgumentException('app.' . $key . ' has not been set');
            }

            foreach ($disallowedCharacters as $char) {
                if (str_contains((string) $app[$key], $char)) {
                    throw new InvalidArgumentException('app.' . $key . ' cannot contain the ' . $char . ' character');
                }
            }
        }
    }

    public function __call($name, $args)
    {
        if (!$this->factory->has($name)) {
            throw new RuntimeException('no api namespace found: ' . $name);
        }

        $collection = $this->factory->get($name);

        if (empty($args)) {
            return $collection;
        }

        return call_user_func_array($collection, $args);
    }

    /**
     * @noinspection MagicMethodsValidityInspection
     */
    public function __get($name)
    {
        if (!$this->factory->has($name)) {
            throw new RuntimeException('no api namespace found: ' . $name);
        }

        return $this->factory->get($name);
    }

    public function getDebug(): mixed
    {
        return $this->debug;
    }

    public function setDebug(mixed $debug): Client
    {
        $this->debug = $debug;
        return $this;
    }

    protected function getVersion(): string
    {
        return InstalledVersions::getVersion('vonage/client-core');
    }

    public function getLogger(): ?LoggerInterface
    {
        if (!$this->logger && $this->getFactory()->has(LoggerInterface::class)) {
            $this->setLogger($this->getFactory()->get(LoggerInterface::class));
        }

        return $this->logger;
    }

    public function getCredentials(): CredentialsInterface
    {
        return $this->credentials;
    }
}
