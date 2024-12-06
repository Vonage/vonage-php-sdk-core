<?php

declare(strict_types=1);

namespace Vonage;

use Composer\InstalledVersions;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Vonage\Account\ClientFactory;
use Vonage\Application\ClientFactory as ApplicationClientFactory;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Basic;
use Vonage\Client\Credentials\Container;
use Vonage\Client\Credentials\CredentialsInterface;
use Vonage\Client\Credentials\Gnp;
use Vonage\Client\Credentials\Keypair;
use Vonage\Client\Credentials\SignatureSecret;
use Vonage\Client\Factory\FactoryInterface;
use Vonage\Client\Factory\MapFactory;
use Vonage\Conversion\ClientFactory as ConversionClientFactory;
use Vonage\Insights\ClientFactory as InsightsClientFactory;
use Vonage\Meetings\ClientFactory as MeetingsClientFactory;
use Vonage\Numbers\ClientFactory as NumbersClientFactory;
use Vonage\NumberVerification\ClientFactory as NumberVerificationClientFactory;
use Vonage\Redact\ClientFactory as RedactClientFactory;
use Vonage\Secrets\ClientFactory as SecretsClientFactory;
use Vonage\SimSwap\ClientFactory as SimSwapClientFactory;
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
use function is_null;
use function set_error_handler;

/**
 * Vonage API Client, allows access to the API from PHP.
 *
 * @method Account\Client account()
 * @method Meetings\Client meetings()
 * @method Messages\Client messages()
 * @method Application\Client applications()
 * @method Conversion\Client conversion()
 * @method Conversation\Client conversation()
 * @method Insights\Client insights()
 * @method Numbers\Client numbers()
 * @method NumberVerification\Client numberVerification()
 * @method Redact\Client redact()
 * @method Secrets\Client secrets()
 * @method SimSwap\Client simswap()
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

    protected CredentialsInterface $credentials;

    protected ClientInterface $client;

    protected mixed $debug = false;

    protected ContainerInterface $factory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected array $options = ['show_deprecations' => false, 'debug' => false];

    /**
     * Create a new API client using the provided credentials.
     */
    public function __construct(
        CredentialsInterface $credentials,
        ?VonageConfig $options = null,
        ?ClientInterface $client = null
    ) {
        if (is_null($client)) {
            // Since the user did not pass a client, try and make a client
            // using the Guzzle 6 adapter or Guzzle 7 (depending on availability)
            [$guzzleVersion] = explode('@', (string) InstalledVersions::getVersion('guzzlehttp/guzzle'), 1);
            $guzzleVersion = (float) $guzzleVersion;

            if ($guzzleVersion >= 6.0 && $guzzleVersion < 7) {
                $client = new \Http\Adapter\Guzzle6\Client();
            }

            if ($guzzleVersion >= 7.0 && $guzzleVersion < 8.0) {
                $client = new \GuzzleHttp\Client();
            }
        }

        if (
            !($credentials instanceof Container) &&
            !($credentials instanceof Basic) &&
            !($credentials instanceof SignatureSecret) &&
            !($credentials instanceof Keypair) &&
            !($credentials instanceof Gnp)
        ) {
            throw new RuntimeException('unknown credentials type: ' . $credentials::class);
        }

        $this->credentials = $credentials;

        $this->options = array_merge($this->options, $options);

        // If they've provided an app name, validate it
        if (isset($options['app'])) {
            $this->validateAppOptions($options['app']);
        }

        $services = [
            // Registered Services by name
            'account' => ClientFactory::class,
            'applications' => ApplicationClientFactory::class,
            'conversion' => ConversionClientFactory::class,
            'conversation' => ConversationClientFactory::class,
            'insights' => InsightsClientFactory::class,
            'numbers' => NumbersClientFactory::class,
            'numberVerification' => NumberVerificationClientFactory::class,
            'meetings' => MeetingsClientFactory::class,
            'messages' => MessagesClientFactory::class,
            'redact' => RedactClientFactory::class,
            'secrets' => SecretsClientFactory::class,
            'simswap' => SimSwapClientFactory::class,
            'sms' => SMSClientFactory::class,
            'subaccount' => SubaccountClientFactory::class,
            'users' => UsersClientFactory::class,
            'verify' => VerifyClientFactory::class,
            'verify2' => Verify2ClientFactory::class,
            'voice' => VoiceClientFactory::class,

            // Additional utility classes
            APIResource::class => APIResource::class,
            Client::class => fn () => $this
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
                static fn (int $errno, string $errstr, string $errfile = null, int $errline = null, array $errorcontext = null) => true,
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
