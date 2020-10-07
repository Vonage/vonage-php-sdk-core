<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
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
use Vonage\Entity\CollectionInterface;
use Vonage\Entity\CollectionTrait;

/**
 * @deprecated Please use Vonage\Voice\Client for this functionality
 */
class Collection implements ClientAwareInterface, CollectionInterface, ArrayAccess
{
    use ClientAwareTrait;
    use CollectionTrait;

    /**
     * Collection constructor.
     */
    public function __construct()
    {
        trigger_error(
            'Vonage\Call\Collection is deprecated, please use Vonage\Voice\Client instead',
            E_USER_DEPRECATED
        );
    }

    /**
     * @return string
     */
    public static function getCollectionName(): string
    {
        return 'calls';
    }

    /**
     * @return string
     */
    public static function getCollectionPath(): string
    {
        return '/v1/' . self::getCollectionName();
    }

    /**
     * @param $data
     * @param $idOrCall
     * @return mixed
     */
    public function hydrateEntity($data, $idOrCall)
    {
        if (!($idOrCall instanceof Call)) {
            $idOrCall = new Call($idOrCall);
        }

        $idOrCall->setClient($this->getClient());
        $idOrCall->jsonUnserialize($data);

        return $idOrCall;
    }

    /**
     * @param null $filter
     * @return $this
     */
    public function __invoke($filter = null): self
    {
        /** Fix for the smarter MapFactory in v2.2.0 and the uniqueness of this class interface */
        if ($filter instanceof Filter) {
            $this->setFilter($filter);
        }

        return $this;
    }

    /**
     * @param $call
     * @return Call
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     */
    public function create($call): Call
    {
        return $this->post($call);
    }

    /**
     * @param $payload
     * @param $idOrCall
     * @return Call
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     */
    public function put($payload, $idOrCall): Call
    {
        if (!($idOrCall instanceof Call)) {
            $idOrCall = new Call($idOrCall);
        }

        $idOrCall->setClient($this->getClient());
        $idOrCall->put($payload);

        return $idOrCall;
    }

    /**
     * @param $call
     * @param $type
     * @return Call
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     * @throws ClientExceptionInterface
     */
    public function delete($call, $type): Call
    {
        if (is_object($call) && is_callable([$call, 'getId'])) {
            $call = $call->getId();
        }

        if (!($call instanceof Call)) {
            $call = new Call($call);
        }

        $request = new Request(
            $this->getClient()->getApiUrl() . self::getCollectionPath() . '/' . $call->getId() . '/' . $type,
            'DELETE'
        );

        $response = $this->client->send($request);

        if ((int)$response->getStatusCode() !== 204) {
            throw $this->getException($response);
        }

        return $call;
    }

    /**
     * @param $call
     * @return Call
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     */
    public function post($call): Call
    {
        if ($call instanceof Call) {
            $body = $call->getRequestData();
        } else {
            $body = $call;
        }

        $request = new Request(
            $this->getClient()->getApiUrl() . self::getCollectionPath(),
            'POST',
            'php://temp',
            ['content-type' => 'application/json']
        );

        $request->getBody()->write(json_encode($body));
        $response = $this->client->send($request);

        if ((int)$response->getStatusCode() !== 201) {
            $e = $this->getException($response);
            $e->setRequest($request);

            throw $e;
        }

        $body = json_decode($response->getBody()->getContents(), true);
        $call = new Call($body['uuid']);
        $call->jsonUnserialize($body);
        $call->setClient($this->getClient());

        return $call;
    }

    /**
     * @param $call
     * @return Call
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     */
    public function get($call): Call
    {
        if (!($call instanceof Call)) {
            $call = new Call($call);
        }

        $call->setClient($this->getClient());
        $call->get();

        return $call;
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

        // Error responses aren't consistent. Some are generated within the
        // proxy and some are generated within voice itself. This handles
        // both cases

        // This message isn't very useful, but we shouldn't ever see it
        $errorTitle = $body['error_title'] ?? $body['title'] ?? 'Unexpected error';

        if ($status >= 400 && $status < 500) {
            $e = new Exception\Request($errorTitle, $status);
        } elseif ($status >= 500 && $status < 600) {
            $e = new Exception\Server($errorTitle, $status);
        } else {
            $e = new Exception\Exception('Unexpected HTTP Status Code');
            throw $e;
        }

        $e->setResponse($response);

        return $e;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        //todo: validate form of id
        return true;
    }

    /**
     * @param mixed $call
     * @return Call
     */
    public function offsetGet($call): Call
    {
        if (!($call instanceof Call)) {
            $call = new Call($call);
        }

        $call->setClient($this->getClient());

        return $call;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        throw new RuntimeException('can not set collection properties');
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        throw new RuntimeException('can not unset collection properties');
    }
}
