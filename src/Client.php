<?php

declare(strict_types=1);

namespace Vonage;

use Composer\InstalledVersions;
use Http\Client\HttpClient;
use InvalidArgumentException;
use Laminas\Diactoros\Request;
use Laminas\Diactoros\Uri;
use Lcobucci\JWT\Token;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RuntimeException;
use Vonage\Account\ClientFactory;
use Vonage\Application\ClientFactory as ApplicationClientFactory;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Basic;
use Vonage\Client\Credentials\Container;
use Vonage\Client\Credentials\CredentialsInterface;
use Vonage\Client\Credentials\Handler\BasicHandler;
use Vonage\Client\Credentials\Handler\SignatureBodyFormHandler;
use Vonage\Client\Credentials\Handler\SignatureBodyHandler;
use Vonage\Client\Credentials\Handler\SignatureQueryHandler;
use Vonage\Client\Credentials\Handler\TokenBodyFormHandler;
use Vonage\Client\Credentials\Handler\TokenBodyHandler;
use Vonage\Client\Credentials\Handler\TokenQueryHandler;
use Vonage\Client\Credentials\Keypair;
use Vonage\Client\Credentials\SignatureSecret;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Client\Factory\FactoryInterface;
use Vonage\Client\Factory\MapFactory;
use Vonage\Conversion\ClientFactory as ConversionClientFactory;
use Vonage\Entity\EntityInterface;
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
use Vonage\Verify\Verification;
use Vonage\Voice\ClientFactory as VoiceClientFactory;
use Vonage\Logger\{LoggerAwareInterface, LoggerTrait};

use function array_key_exists;
use function array_merge;
use function call_user_func_array;
use function http_build_query;
use function implode;
use function is_null;
use function json_encode;
use function method_exists;
use function set_error_handler;
use function str_replace;
use function strpos;

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

    protected Container|Keypair|SignatureSecret|CredentialsInterface|Basic $credentials;

    protected ContainerInterface $factory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Create a new API client using the provided credentials.
     */
    public function __construct(
        CredentialsInterface $credentials,
        protected bool $debug = false,
        protected bool $showDeprecations = false,
        protected ?array $app = null,
        ?ClientInterface $client = null
    ) {

        // Make sure we know how to use the credentials
        if (
            !($credentials instanceof Container) &&
            !($credentials instanceof Basic) &&
            !($credentials instanceof SignatureSecret) &&
            !($credentials instanceof Keypair)
        ) {
            throw new RuntimeException('unknown credentials type: ' . $credentials::class);
        }

        $this->credentials = $credentials;

        // If they've provided an app name, validate it
        if (!is_null($this->app)) {
            $this->validateAppOptions($this->app);
        }

        // Configure a base HTTPClient Object
        $httpClient = new \Vonage\Client\HttpClient(
            $this->debug,
            $this->app,
            $client
        );

        $services = [
            // Registered Services by name
            'account' => ClientFactory::class,
            'applications' => ApplicationClientFactory::class,
            'conversion' => ConversionClientFactory::class,
            'conversation' => ConversationClientFactory::class,
            'insights' => InsightsClientFactory::class,
            'numbers' => NumbersClientFactory::class,
            'meetings' => MeetingsClientFactory::class,
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
            APIResource::class => APIResource::class,
            'credentials' => $this->credentials,
            \Vonage\Client\HttpClient::class => $httpClient,
        ];

        if (class_exists('Vonage\Video\ClientFactory')) {
            $services['video'] = 'Vonage\Video\ClientFactory';
        } else {
            $services['video'] = function () {
                throw new \RuntimeException('Please install @vonage/video to use the Video API');
            };
        }

        $this->setFactory(
            new MapFactory(
                $services,
                $this
            )
        );

        if ($this->showDeprecations) {
            set_error_handler(
                static function (
                    int $errno,
                    string $errstr,
                    string $errfile = null,
                    int $errline = null,
                    array $errorcontext = null
                ) {
                    return true;
                },
                E_USER_DEPRECATED
            );
        }
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
                if (strpos($app[$key], $char) !== false) {
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
