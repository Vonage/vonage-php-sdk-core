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
use Exception;
use Laminas\Diactoros\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Vonage\Client\ClientAwareInterface;
use Vonage\Client\ClientAwareTrait;
use Vonage\Client\Exception as ClientException;
use Vonage\Entity\CollectionInterface;
use Vonage\Entity\CollectionTrait;
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\JsonSerializableTrait;
use Vonage\Entity\NoRequestResponseTrait;

use function is_null;
use function json_decode;
use function json_encode;

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

    public static function getCollectionName(): string
    {
        return 'conversations';
    }

    public static function getCollectionPath(): string
    {
        return '/beta/' . self::getCollectionName();
    }

    /**
     * @param $data
     * @param $idOrConversation
     *
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
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function create($conversation): Conversation
    {
        return $this->post($conversation);
    }

    /**
     * @param $conversation
     *
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     * @throws ClientExceptionInterface
     * @throws Exception
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
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
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
     * @throws ClientException\Exception
     *
     * @return ClientException\Request|ClientException\Server
     */
    protected function getException(ResponseInterface $response)
    {
        $body = json_decode($response->getBody()->getContents(), true);
        $status = $response->getStatusCode();

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

    public function offsetExists($offset): bool
    {
        return true;
    }

    public function offsetGet($conversation): Conversation
    {
        if (!($conversation instanceof Conversation)) {
            $conversation = new Conversation($conversation);
        }

        $conversation->setClient($this->getClient());
        return $conversation;
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
