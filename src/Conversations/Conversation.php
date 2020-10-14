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
use Vonage\Client\Exception;
use Vonage\Entity\EntityInterface;
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\JsonSerializableTrait;
use Vonage\Entity\JsonUnserializableInterface;
use Vonage\Entity\NoRequestResponseTrait;
use Vonage\User\Collection as UserCollection;
use Vonage\User\User;

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

    /**
     * Conversation constructor.
     *
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->data['id'] = $id;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name): Conversation
    {
        $this->data['name'] = $name;

        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setDisplayName($name): Conversation
    {
        $this->data['display_name'] = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->data['uuid'] ?? $this->data['id'];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->getId();
    }


    /**
     * @return $this
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     * @throws ClientExceptionInterface
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
    public function jsonSerialize()
    {
        return $this->data;
    }

    /**
     * @param array $json
     * @return void|null
     */
    public function jsonUnserialize(array $json): void
    {
        $this->data = $json;
    }

    /**
     * @return array
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
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
     * @param User $user
     * @return User
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     */
    public function addMember(User $user): User
    {
        return $this->sendPostAction($user, 'join');
    }

    /**
     * @param User $user
     * @return User
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     */
    public function inviteMember(User $user): User
    {
        return $this->sendPostAction($user, 'invite');
    }

    /**
     * @param User $user
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
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
     * @param User $user
     * @param $action
     * @param string $channel
     * @return User
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
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
     * @param ResponseInterface $response
     * @return Exception\Request|Exception\Server
     * @throws Exception\Exception
     */
    protected function getException(ResponseInterface $response)
    {
        $body = json_decode($response->getBody()->getContents(), true);
        $status = (int)$response->getStatusCode();

        // This message isn't very useful, but we shouldn't ever see it
        $errorTitle = $body['error_title'] ?? $body['description'] ?? 'Unexpected error';

        if ($status >= 400 && $status < 500) {
            $e = new Exception\Request($errorTitle, $status);
        } elseif ($status >= 500 && $status < 600) {
            $e = new Exception\Server($errorTitle, $status);
        } else {
            $e = new Exception\Exception('Unexpected HTTP Status Code');
            throw $e;
        }

        return $e;
    }
}
