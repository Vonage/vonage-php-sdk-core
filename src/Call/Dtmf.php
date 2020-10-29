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
 * @deprecated Use Vonage\Voice\Client::playDTMF() method instead
 */
class Dtmf implements JsonSerializableInterface, ClientAwareInterface, ArrayAccess
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
     * @var string[]
     */
    protected $params = ['digits'];

    public function __construct(?string $id = null)
    {
        trigger_error(
            'Vonage\Call\Dtmf is deprecated, please use Vonage\Voice\Client::playDTMF() instead',
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

    public function setDigits($digits): void
    {
        $this->data['digits'] = (string)$digits;
    }

    /**
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     * @throws ClientExceptionInterface
     */
    public function put(?self $dtmf = null): Event
    {
        if (!$dtmf) {
            $dtmf = $this;
        }

        $request = new Request(
            $this->getClient()->getApiUrl() . Collection::getCollectionPath() . '/' . $this->getId() . '/dtmf',
            'PUT',
            'php://temp',
            ['content-type' => 'application/json']
        );

        $request->getBody()->write(json_encode($dtmf));
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
        if (!in_array($offset, $this->params, false)) {
            throw new RuntimeException('invalid parameter: ' . $offset);
        }

        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        if (!in_array($offset, $this->params, false)) {
            throw new RuntimeException('invalid parameter: ' . $offset);
        }

        unset($this->data[$offset]);
    }
}
