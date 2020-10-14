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
 * @deprecated Use Vonage\Voice\Client::playDTMF() method instead
 */
class Dtmf implements JsonSerializableInterface, ClientAwareInterface, ArrayAccess
{
    use ClientAwareTrait;

    protected $id;

    protected $data = [];

    protected $params = [
        'digits'
    ];

    /**
     * Dtmf constructor.
     *
     * @param string|null $id
     */
    public function __construct(?string $id = null)
    {
        trigger_error(
            'Vonage\Call\Dtmf is deprecated, please use Vonage\Voice\Client::playDTMF() instead',
            E_USER_DEPRECATED
        );

        $this->id = $id;
    }

    /**
     * @param Dtmf|null $entity
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
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param $digits
     */
    public function setDigits($digits): void
    {
        $this->data['digits'] = (string)$digits;
    }

    /**
     * @param null $dtmf
     * @return Event
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     * @throws ClientExceptionInterface
     */
    public function put($dtmf = null): Event
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
        $status = $response->getStatusCode();

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
