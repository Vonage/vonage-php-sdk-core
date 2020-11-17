<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Call;

use ArrayAccess;
use Laminas\Diactoros\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Vonage\Client\ClientAwareInterface;
use Vonage\Client\ClientAwareTrait;
use Vonage\Client\Exception as ClientException;
use Vonage\Entity\JsonSerializableInterface;

use function in_array;
use function is_null;
use function json_decode;
use function json_encode;
use function trigger_error;

/**
 * Lightweight resource, only has put / delete.
 *
 * @deprecated Please use Vonage\Voice\Client::playTTS() instead
 */
class Talk implements JsonSerializableInterface, ClientAwareInterface, ArrayAccess
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

    /**
     * @var array
     */
    protected $params = [
        'text',
        'voice_name',
        'loop'
    ];

    public function __construct(?string $id = null)
    {
        trigger_error(
            'Vonage\Call\Talk is deprecated, please use Vonage\Voice\Client::playTTS() ' .
            'and Vonage\Voice\Client::stopTTS() instead',
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
    public function __invoke(?self $entity = null)
    {
        if (is_null($entity)) {
            return $this;
        }

        return $this->put($entity);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setText($text): void
    {
        $this->data['text'] = (string)$text;
    }

    public function setVoiceName($name): void
    {
        $this->data['voice_name'] = (string)$name;
    }

    /**
     * @param string|int $times
     */
    public function setLoop($times): void
    {
        $this->data['loop'] = (int)$times;
    }

    /**
     * @param null|mixed $talk
     *
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     * @throws ClientExceptionInterface
     */
    public function put($talk = null): Event
    {
        if (!$talk) {
            $talk = $this;
        }

        $request = new Request(
            $this->getClient()->getApiUrl() . Collection::getCollectionPath() . '/' . $this->getId() . '/talk',
            'PUT',
            'php://temp',
            ['content-type' => 'application/json']
        );

        $request->getBody()->write(json_encode($talk));
        $response = $this->client->send($request);

        return $this->parseEventResponse($response);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function delete(): Event
    {
        $request = new Request(
            $this->getClient()->getApiUrl() . Collection::getCollectionPath() . '/' . $this->getId() . '/talk',
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
     *
     * @return ClientException\Request|ClientException\Server
     */
    protected function getException(ResponseInterface $response)
    {
        $body = json_decode($response->getBody()->getContents(), true);
        $status = (int)$response->getStatusCode();

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

    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        if (!in_array($offset, $this->params, true)) {
            throw new RuntimeException('invalid parameter: ' . $offset);
        }

        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        if (!in_array($offset, $this->params, true)) {
            throw new RuntimeException('invalid parameter: ' . $offset);
        }

        unset($this->data[$offset]);
    }
}
