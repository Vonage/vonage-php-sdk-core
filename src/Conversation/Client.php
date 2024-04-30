<?php

namespace Vonage\Conversation;

use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Conversation\ConversationObjects\Conversation;
use Vonage\Conversation\Filter\ListConversationFilter;
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
        $response = $this->api->search($conversationFilter);
        $response->setHasPagination(false);
        $response->setNaiveCount(true);

        $hydrator = new ArrayHydrator();
        $hydrator->setPrototype(new Conversation());
        $response->setHydrator($hydrator);

        return $response;
    }
}
