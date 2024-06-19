<?php

declare(strict_types=1);

namespace Vonage\Client;

use Composer\InstalledVersions;
use Laminas\Diactoros\Uri;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LogLevel;
use Vonage\Logger\LoggerTrait;

class HttpClient
{
    use LoggerTrait;

    public string $apiUrl = 'https://api.nexmo.com';

    public string $restUrl = 'https://rest.nexmo.com';

    protected bool $debug = false;

    public function __construct(
        protected ?ClientInterface $httpClientLibrary = null,
        protected array $options = ['show_deprecations' => false, 'debug' => false]
    ) {
        if (is_null($httpClientLibrary)) {
            // Since the user did not pass a client, try and make a client
            // using the Guzzle 6 adapter or Guzzle 7 (depending on availability)
            [$guzzleVersion] = explode('@', InstalledVersions::getVersion('guzzlehttp/guzzle'), 1);
            $guzzleVersion = (float) $guzzleVersion;

            if ($guzzleVersion >= 6.0 && $guzzleVersion < 7) {
                /** @noinspection PhpUndefinedNamespaceInspection */
                /** @noinspection PhpUndefinedClassInspection */
                $client = new \Http\Adapter\Guzzle6\Client();
            }

            if ($guzzleVersion >= 7.0 && $guzzleVersion < 8.0) {
                $client = new \GuzzleHttp\Client();
            }
        }

        $this->setHttpClientLibrary($client);

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
     * This allows the default http client to be swapped out for a HTTPlug compatible
     * replacement.
     */
    public function setHttpClientLibrary(ClientInterface $client): self
    {
        $this->httpClientLibrary = $client;

        return $this;
    }

    public function getHttpClient(): ClientInterface
    {
        return $this->httpClientLibrary;
    }

    protected function getVersion(): string
    {
        return InstalledVersions::getVersion('vonage/client-core');
    }

    public function send(RequestInterface $request): ResponseInterface
    {
        // Allow any part of the URI to be replaced with a simple search
        if (isset($this->options['url'])) {
            foreach ($this->options['url'] as $search => $replace) {
                $uri = (string)$request->getUri();
                $new = str_replace($search, $replace, $uri);

                if ($uri !== $new) {
                    $request = $request->withUri(new Uri($new));
                }
            }
        }

        // The user agent must be in the following format:
        // LIBRARY-NAME/LIBRARY-VERSION LANGUAGE-NAME/LANGUAGE-VERSION [APP-NAME/APP-VERSION]
        // See https://github.com/Vonage/client-library-specification/blob/master/SPECIFICATION.md#reporting
        $userAgent = [];

        // Library name
        $userAgent[] = 'vonage-php/' . $this->getVersion();

        // Language name
        $userAgent[] = 'php/' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;

        // If we have an app set, add that to the UA
        if (isset($this->options['app'])) {
            $app = $this->options['app'];
            $userAgent[] = $app['name'] . '/' . $app['version'];
        }

        // Set the header. Build by joining all the parts we have with a space
        $request = $request->withHeader('User-Agent', implode(' ', $userAgent));
        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $response = $this->httpClientLibrary->sendRequest($request);

        if ($this->debug) {
            $id = uniqid('', true);
            $request->getBody()->rewind();
            $response->getBody()->rewind();
            $this->log(
                LogLevel::DEBUG,
                'Request ' . $id,
                [
                    'url' => $request->getUri()->__toString(),
                    'headers' => $request->getHeaders(),
                    'body' => explode("\n", $request->getBody()->__toString())
                ]
            );
            $this->log(
                LogLevel::DEBUG,
                'Response ' . $id,
                [
                    'headers ' => $response->getHeaders(),
                    'body' => explode("\n", $response->getBody()->__toString())
                ]
            );

            $request->getBody()->rewind();
            $response->getBody()->rewind();
        }

        return $response;
    }
}
