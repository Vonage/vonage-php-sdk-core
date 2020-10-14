<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\Conversations;

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
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\JsonSerializableTrait;
use Vonage\Entity\NoRequestResponseTrait;

/**
 * @deprecated Conversations is not released for General Availability and will be removed in a future release
 */
class Collection implements ClientAwareInterface, CollectionInterface, ArrayAccess
{
    use ClientAwareTrait;
    use CollectionTrait;
    use JsonSerializableTrait;
    use NoRequestResponseTrait;
    use JsonResponseTrait;

    /**
     * @return string
     */
    public static function getCollectionName(): string
    {
        return 'conversations';
    }

    /**
     * @return string
     */
    public static function getCollectionPath(): string
    {
        return '/beta/' . self::getCollectionName();
    }

    /**
     * @param $data
     * @param $idOrConversation
     * @return mixed|Conversation
     */
    public function hydrateEntity($data, $idOrConversation)
    {
        if (!($idOrConversation instanceof Conversation)) {
            $idOrConversation = new Conversation($idOrConversation);
        }

        $idOrConversation->setClient($this->getClient());
        $idOrConversation->jsonUnserialize($data);

        return $idOrConversation;
    }

    /**
     * @param $conversations
     * @return array
     */
    public function hydrateAll($conversations): array
    {
        $hydrated = [];

        foreach ($conversations as $conversation) {
            $hydrated[] = $this->hydrateEntity($conversation, $conversation['id']);
        }

        return $hydrated;
    }

    /**
     * @param mixed $filter
     * @return $this
     */
    public function __invoke($filter = null)
    {
        if (!is_null($filter)) {
            $this->setFilter($filter);
        }

        return $this;
    }

    /**
     * @param $conversation
     * @return Conversation
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     */
    public function create($conversation): Conversation
    {
        return $this->post($conversation);
    }

    /**
     * @param $conversation
     * @return Conversation
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     * @throws ClientExceptionInterface
     * @throws \Exception
     */
    public function post($conversation): Conversation
    {
        if ($conversation instanceof Conversation) {
            $body = $conversation->getRequestData();
        } else {
            $body = $conversation;
        }

        $request = new Request(
            $this->getClient()->getApiUrl() . self::getCollectionPath(),
            'POST',
            'php://temp',
            ['content-type' => 'application/json']
        );

        $request->getBody()->write(json_encode($body));
        $response = $this->getClient()->send($request);

        if ((int)$response->getStatusCode() !== 200) {
            throw $this->getException($response);
        }

        $body = json_decode($response->getBody()->getContents(), true);
        $conversation = new Conversation($body['id']);
        $conversation->jsonUnserialize($body);
        $conversation->setClient($this->getClient());

        return $conversation;
    }

    /**
     * @param $conversation
     * @return Conversation
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     */
    public function get($conversation): Conversation
    {
        if (!($conversation instanceof Conversation)) {
            $conversation = new Conversation($conversation);
        }

        $conversation->setClient($this->getClient());
        $conversation->get();

        return $conversation;
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

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return true;
    }

    /**
     * @param mixed $conversation
     * @return Conversation
     */
    public function offsetGet($conversation): Conversation
    {
        if (!($conversation instanceof Conversation)) {
            $conversation = new Conversation($conversation);
        }

        $conversation->setClient($this->getClient());
        return $conversation;
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
