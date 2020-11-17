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
use Vonage\Entity\CollectionInterface;
use Vonage\Entity\CollectionTrait;

use function is_callable;
use function is_object;
use function json_decode;
use function json_encode;
use function trigger_error;

/**
 * @deprecated Please use Vonage\Voice\Client for this functionality
 */
class Collection implements ClientAwareInterface, CollectionInterface, ArrayAccess
{
    use ClientAwareTrait;
    use CollectionTrait;

    public function __construct()
    {
        trigger_error(
            'Vonage\Call\Collection is deprecated, please use Vonage\Voice\Client instead',
            E_USER_DEPRECATED
        );
    }

    public static function getCollectionName(): string
    {
        return 'calls';
    }

    public static function getCollectionPath(): string
    {
        return '/v1/' . self::getCollectionName();
    }

    public function hydrateEntity($data, $idOrCall): Call
    {
        if (!($idOrCall instanceof Call)) {
            $idOrCall = new Call($idOrCall);
        }

        $idOrCall->setClient($this->getClient());
        $idOrCall->jsonUnserialize($data);

        return $idOrCall;
    }

    public function __invoke($filter = null): self
    {
        /** Fix for the smarter MapFactory in v2.2.0 and the uniqueness of this class interface */
        if ($filter instanceof Filter) {
            $this->setFilter($filter);
        }

        return $this;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function create($call): Call
    {
        return $this->post($call);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
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
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
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
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
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
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
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
     * @throws ClientException\Exception
     *
     * @return ClientException\Request|ClientException\Server
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
            $e = new ClientException\Request($errorTitle, $status);
        } elseif ($status >= 500 && $status < 600) {
            $e = new ClientException\Server($errorTitle, $status);
        } else {
            $e = new ClientException\Exception('Unexpected HTTP Status Code');
            throw $e;
        }

        $e->setResponse($response);

        return $e;
    }

    public function offsetExists($offset): bool
    {
        //todo: validate form of id
        return true;
    }

    public function offsetGet($call): Call
    {
        if (!($call instanceof Call)) {
            $call = new Call($call);
        }

        $call->setClient($this->getClient());

        return $call;
    }

    public function offsetSet($offset, $value): void
    {
        throw new RuntimeException('can not set collection properties');
    }

    public function offsetUnset($offset): void
    {
        throw new RuntimeException('can not unset collection properties');
    }
}
