<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Call;

use Laminas\Diactoros\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Vonage\Client\ClientAwareInterface;
use Vonage\Client\ClientAwareTrait;
use Vonage\Client\Exception as ClientException;
use Vonage\Entity\JsonSerializableInterface;

use function is_array;
use function is_null;
use function json_decode;
use function json_encode;
use function trigger_error;

/**
 * Lightweight resource, only has put / delete.
 *
 * @deprecated Please use Vonage\Voice\Client::streamAudio() or Vonage\Voice\Client::stopStreamAudio() instead
 */
class Stream implements JsonSerializableInterface, ClientAwareInterface
{
    use ClientAwareTrait;

    /**
     * @var string|null
     */
    protected $id;

    /**
     * @var array
     */
    protected $data = [];

    public function __construct(?string $id = null)
    {
        trigger_error(
            'Vonage\Call\Stream is deprecated, ' .
            'please use Vonage\Voice\Client::streamAudio() or Vonage\Voice\Client::stopStreamAudio() instead',
            E_USER_DEPRECATED
        );

        $this->id = $id;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     *
     * @return $this|Event
     */
    public function __invoke(?Stream $stream = null)
    {
        if (is_null($stream)) {
            return $this;
        }

        return $this->put($stream);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setUrl($url): void
    {
        if (!is_array($url)) {
            $url = [$url];
        }

        $this->data['stream_url'] = $url;
    }

    /**
     * @param string|int $times
     */
    public function setLoop($times): void
    {
        $this->data['loop'] = (int)$times;
    }

    /**
     * @param null|mixed $stream
     *
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     * @throws ClientExceptionInterface
     */
    public function put($stream = null): Event
    {
        if (!$stream) {
            $stream = $this;
        }

        $request = new Request(
            $this->getClient()->getApiUrl() . Collection::getCollectionPath() . '/' . $this->getId() . '/stream',
            'PUT',
            'php://temp',
            ['content-type' => 'application/json']
        );

        $request->getBody()->write(json_encode($stream));
        $response = $this->client->send($request);

        return $this->parseEventResponse($response);
    }

    /**
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     * @throws ClientExceptionInterface
     */
    public function delete(): Event
    {
        $request = new Request(
            $this->getClient()->getApiUrl() . Collection::getCollectionPath() . '/' . $this->getId() . '/stream',
            'DELETE'
        );

        $response = $this->client->send($request);

        return $this->parseEventResponse($response);
    }

    /**
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    protected function parseEventResponse(ResponseInterface $response): Event
    {
        if ((int)$response->getStatusCode() !== 200) {
            throw $this->getException($response);
        }

        $json = json_decode($response->getBody()->getContents(), true);

        if (!$json) {
            throw new ClientException\Exception('Unexpected Response Body Format');
        }

        return new Event($json);
    }

    /**
     * @throws ClientException\Exception
     */
    protected function getException(ResponseInterface $response)
    {
        $body = json_decode($response->getBody()->getContents(), true);
        $status = $response->getStatusCode();

        if ($status >= 400 && $status < 500) {
            $e = new ClientException\Request($body['error_title'], $status);
        } elseif ($status >= 500 && $status < 600) {
            $e = new ClientException\Server($body['error_title'], $status);
        } else {
            $e = new ClientException\Exception('Unexpected HTTP Status Code');
            throw $e;
        }

        return $e;
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
