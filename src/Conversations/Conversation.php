<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Conversations;

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
use Vonage\User\Collection as UserCollection;
use Vonage\User\User;

use function json_decode;

/**
 * @deprecated Conversations is not released for General Availability and will be removed in a future release
 */
class Conversation implements EntityInterface, JsonSerializable, JsonUnserializableInterface, ClientAwareInterface
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
    public function setName($name): Conversation
    {
        $this->data['name'] = $name;

        return $this;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function setDisplayName($name): Conversation
    {
        $this->data['display_name'] = $name;

        return $this;
    }

    public function getId()
    {
        return $this->data['uuid'] ?? $this->data['id'];
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
    public function get(): Conversation
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

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function members(): array
    {
        $apiUrl = $this->getClient()->getApiUrl();
        $response = $this->getClient()->get(
            $apiUrl . Collection::getCollectionPath() . '/' . $this->getId() . '/members'
        );

        if ((int)$response->getStatusCode() !== 200) {
            throw $this->getException($response);
        }

        $data = json_decode($response->getBody()->getContents(), true);
        $memberCollection = new UserCollection();

        return $memberCollection->hydrateAll($data);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function addMember(User $user): User
    {
        return $this->sendPostAction($user, 'join');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function inviteMember(User $user): User
    {
        return $this->sendPostAction($user, 'invite');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function removeMember(User $user): void
    {
        $apiUrl = $this->getClient()->getApiUrl();
        $response = $this->getClient()->delete(
            $apiUrl . Collection::getCollectionPath() . '/' . $this->getId() . '/members/' . $user->getId()
        );

        if ((int)$response->getStatusCode() !== 200) {
            throw $this->getException($response);
        }
    }

    /**
     * @param $action
     * @param string $channel
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function sendPostAction(User $user, $action, $channel = 'app'): User
    {
        $body = $user->getRequestDataForConversation();
        $body['action'] = $action;
        $body['channel'] = ['type' => $channel];

        $response = $this->getClient()->post(
            $this->getClient()->getApiUrl() . Collection::getCollectionPath() . '/' . $this->getId() . '/members',
            $body
        );

        if ((int)$response->getStatusCode() !== 200) {
            throw $this->getException($response);
        }

        $body = json_decode($response->getBody()->getContents(), true);

        $user = new User($body['user_id']);
        $user->jsonUnserialize($body);
        $user->setClient($this->getClient());

        return $user;
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
        $errorTitle = $body['error_title'] ?? $body['description'] ?? 'Unexpected error';

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
