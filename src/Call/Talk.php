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
use Vonage\Client\Exception;
use Vonage\Entity\JsonSerializableInterface;

/**
 * Lightweight resource, only has put / delete.
 *
 * @deprecated Please use Vonage\Voice\Client::playTTS() instead
 */
class Talk implements JsonSerializableInterface, ClientAwareInterface, ArrayAccess
{
    use ClientAwareTrait;

    protected $id;

    protected $data = [];

    protected $params = [
        'text',
        'voice_name',
        'loop'
    ];

    /**
     * Talk constructor.
     *
     * @param null $id
     */
    public function __construct($id = null)
    {
        trigger_error(
            'Vonage\Call\Talk is deprecated, please use Vonage\Voice\Client::playTTS() ' .
            'and Vonage\Voice\Client::stopTTS() instead',
            E_USER_DEPRECATED
        );

        $this->id = $id;
    }

    /**
     * @param Talk|null $entity
     * @return $this|Event
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     */
    public function __invoke(self $entity = null)
    {
        if (is_null($entity)) {
            return $this;
        }

        return $this->put($entity);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $text
     */
    public function setText($text): void
    {
        $this->data['text'] = (string)$text;
    }

    /**
     * @param $name
     */
    public function setVoiceName($name): void
    {
        $this->data['voice_name'] = (string)$name;
    }

    /**
     * @param $times
     */
    public function setLoop($times): void
    {
        $this->data['loop'] = (int)$times;
    }

    /**
     * @param null $talk
     * @return Event
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
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
     * @return Event
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
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
     * @param ResponseInterface $response
     * @return Event
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     */
    protected function parseEventResponse(ResponseInterface $response): Event
    {
        if ((int)$response->getStatusCode() !== 200) {
            throw $this->getException($response);
        }

        $json = json_decode($response->getBody()->getContents(), true);

        if (!$json) {
            throw new Exception\Exception('Unexpected Response Body Format');
        }

        return new Event($json);
    }

    /**
     * @param ResponseInterface $response
     * @return Exception\Request|Exception\Server
     * @throws Exception\Exception
     */
    protected function getException(ResponseInterface $response)
    {
        $body = json_decode($response->getBody()->getContents(), true);
        $status = (int)$response->getStatusCode();

        if ($status >= 400 && $status < 500) {
            $e = new Exception\Request($body['error_title'], $status);
        } elseif ($status >= 500 && $status < 600) {
            $e = new Exception\Server($body['error_title'], $status);
        } else {
            $e = new Exception\Exception('Unexpected HTTP Status Code');
            throw $e;
        }

        return $e;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return $this->data;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        if (!in_array($offset, $this->params, false)) {
            throw new RuntimeException('invalid parameter: ' . $offset);
        }

        $this->data[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        if (!in_array($offset, $this->params, false)) {
            throw new RuntimeException('invalid parameter: ' . $offset);
        }

        unset($this->data[$offset]);
    }
}
