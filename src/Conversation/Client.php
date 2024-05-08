<?php

namespace Vonage\Conversation;

use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Conversation\ConversationObjects\Conversation;
use Vonage\Conversation\ConversationObjects\CreateConversationRequest;
use Vonage\Conversation\ConversationObjects\UpdateConversationRequest;
use Vonage\Conversation\Filter\ListConversationFilter;
use Vonage\Conversation\Filter\ListUserConversationsFilter;
use Vonage\Entity\Hydrator\ArrayHydrator;
use Vonage\Entity\IterableAPICollection;

class Client implements APIClient
{
    public function __construct(protected APIResource $api)
    {
    }

    public function getAPIResource(): APIResource
    {
        return $this->api;
    }

    public function listConversations(ListConversationFilter $conversationFilter = null): IterableAPICollection
    {
        if (!$conversationFilter) {
            $conversationFilter = new ListConversationFilter();
        }

        $response = $this->getApiResource()->search($conversationFilter);
        $response->setHasPagination(false);
        $response->setNaiveCount(true);

        $hydrator = new ArrayHydrator();
        $hydrator->setPrototype(new Conversation());
        $response->setHydrator($hydrator);

        return $response;
    }

    public function createConversation(CreateConversationRequest $createConversation): Conversation
    {
        $response = $this->getApiResource()->create($createConversation->toArray());
        $conversation = new Conversation();
        $conversation->fromArray($response);

        return $conversation;
    }

    public function getConversationById(string $id): Conversation
    {
        $response = $this->getApiResource()->get($id);
        $conversation = new Conversation();
        $conversation->fromArray($response);

        return $conversation;
    }

    public function updateConversationById(string $id, UpdateConversationRequest $updateRequest): Conversation
    {
        $response = $this->getApiResource()->update($id, $updateRequest->toArray());
        $conversation = new Conversation();
        $conversation->fromArray($response);

        return $conversation;
    }

    public function deleteConversationById(string $id): bool
    {
        $this->getApiResource()->delete($id);

        return true;
    }

    public function listUserConversationsByUserId(
        string $userId,
        ?ListUserConversationsFilter $filter = null
    ): IterableAPICollection {
        $api = clone $this->getAPIResource();
        $api->setBaseUrl('https://api.nexmo.com/v1/users');
        $response = $api->search($filter, '/' . $userId . '/conversations');
        $response->setHasPagination(true);
        $response->setNaiveCount(true);

        $hydrator = new ArrayHydrator();
        $hydrator->setPrototype(new Conversation());
        $response->setHydrator($hydrator);

        return $response;
    }
}
