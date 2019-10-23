<?php

namespace Nexmo\Conversations\Event;

use Nexmo\Client\ClientAwareInterface;
use Nexmo\Client\ClientAwareTrait;
use Nexmo\Client\OpenAPIResource;
use Nexmo\Conversations\Conversation;
use Nexmo\Entity\Collection;
use Nexmo\Entity\FilterInterface;

class Client implements ClientAwareInterface
{
    use ClientAwareTrait;

    /**
     * @var OpenAPIResource
     */
    protected $api;

    /**
     * @var Hydrator
     */
    protected $hydrator;

    public function __construct(OpenAPIResource $api, Hydrator $hydrator)
    {
        $this->api = $api;
        $this->hydrator = $hydrator;
    }

    public function create(Event $event) : Event
    {
        $body = [
            'type' => $event->getType(),
            'body' => $event->getBody(),
            'to' => $event->getTo(),
            'from' => $event->getFrom(),
        ];

        $response = $this->getApi()->create($body);
        $event = $this->hydrator->hydrate($response);

        return $event;
    }

    public function delete(Event $event) : void
    {
        if (is_null($event->getId())) {
            throw new \RuntimeException('Event has no ID and cannot be deleted');
        }

        $this->getApi()->delete($event->getId());
    }

    public function get(string $id) : Event
    {
        $data = $this->getApi()->get($id);
        $event = $this->hydrator->hydrate($data);

        return $event;
    }

    public function getApi() : OpenAPIResource
    {
        return $this->api;
    }

    public function search(FilterInterface $filter = null) : Collection
    {
        $collection = $this->getApi()->search($filter);
        $collection->setHydrator($this->hydrator);

        return $collection;
    }

    public function setConversation(Conversation $conversation)
    {
        $this->api->setBaseUri('/v0.1/conversations/' . $conversation->getId() . '/events');
        $this->api->setCollectionName('events');
    }
}
