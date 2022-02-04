<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\User;

use JsonSerializable;
use Laminas\Diactoros\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Vonage\Client\ClientAwareInterface;
use Vonage\Client\ClientAwareTrait;
use Vonage\Client\Exception as ClientException;
use Vonage\Entity\EntityInterface;
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\JsonSerializableTrait;
use Vonage\Entity\JsonUnserializableInterface;
use Vonage\Entity\NoRequestResponseTrait;

use function json_decode;

/**
 * @deprecated This will be removed in a future version, as this API is still considered Beta
 */
class User implements EntityInterface, JsonSerializable, JsonUnserializableInterface, ClientAwareInterface
{
    use NoRequestResponseTrait;
    use JsonSerializableTrait;
    use JsonResponseTrait;
    use ClientAwareTrait;

    protected $data = [];

    public function __construct($id = null)
    {
        $this->data['id'] = $id;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function setName($name): self
    {
        $this->data['name'] = $name;

        return $this;
    }

    public function getId()
    {
        return $this->data['id'];
    }

    public function __toString(): string
    {
        return (string)$this->getId();
    }

    /**
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     * @throws ClientExceptionInterface
     *
     * @return $this
     */
    public function get(): self
    {
        $request = new Request(
            $this->getClient()->getApiUrl() . Collection::getCollectionPath() . '/' . $this->getId(),
            'GET'
        );

        $response = $this->getClient()->send($request);

        if ((int)$response->getStatusCode() !== 200) {
            throw $this->getException($response);
        }

        $data = json_decode($response->getBody()->getContents(), true);
        $this->jsonUnserialize($data);

        return $this;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function getConversations()
    {
        $response = $this->getClient()->get(
            $this->getClient()->getApiUrl() . Collection::getCollectionPath() . '/' . $this->getId() . '/conversations'
        );

        if ((int)$response->getStatusCode() !== 200) {
            throw $this->getException($response);
        }

        $data = json_decode($response->getBody()->getContents(), true);
        $conversationCollection = $this->getClient()->conversation();

        return $conversationCollection->hydrateAll($data);
    }

    /**
     * @return array|mixed
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->data;
    }

    /**
     * @return void|null
     */
    public function jsonUnserialize(array $json): void
    {
        $this->data = $json;
    }

    public function getRequestDataForConversation(): array
    {
        return [
            'user_id' => $this->getId()
        ];
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

        // This message isn't very useful, but we shouldn't ever see it
        $errorTitle = $body['code'] ?? 'Unexpected error';

        if (isset($body['description']) && $body['description']) {
            $errorTitle = $body['description'];
        }

        if (isset($body['error_title'])) {
            $errorTitle = $body['error_title'];
        }

        if ($status >= 400 && $status < 500) {
            $e = new ClientException\Request($errorTitle, $status);
        } elseif ($status >= 500 && $status < 600) {
            $e = new ClientException\Server($errorTitle, $status);
        } else {
            $e = new ClientException\Exception('Unexpected HTTP Status Code');
            throw $e;
        }

        return $e;
    }
}
