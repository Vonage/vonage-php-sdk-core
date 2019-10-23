<?php

namespace Nexmo\Conversations;

use Nexmo\Client\ClientAwareInterface;
use Nexmo\Client\ClientAwareTrait;
use Nexmo\Client\OpenAPIResource;
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

    public function create(Conversation $conversation) : Conversation
    {
        $body = [
            'name' => $conversation->getName(),
            'display_name' => $conversation->getDisplayName(),
            'image_url' => $conversation->getImageUrl(),
            'properties' => $conversation->getProperties(),
        ];

        $response = $this->getApi()->create($body);
        $conversation = $this->hydrator->hydrate($response);

        return $conversation;
    }

    public function delete(Conversation $conversation) : void
    {
        if (is_null($conversation->getId())) {
            throw new \RuntimeException('Conversation does not have an ID and cannot be deleted');
        }

        $this->getApi()->delete($conversation->getId());
    }

    public function get(string $id) : Conversation
    {
        $data = $this->getApi()->get($id);
        $conversation = $this->hydrator->hydrate($data);

        return $conversation;
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

    public function update(Conversation $conversation) : Conversation
    {
        if (is_null($conversation->getId())) {
            throw new \RuntimeException(
                'Conversation does not have an ID and cannot be updated. Please create conversation first.'
            );
        }

        $body = [
            'name' => $conversation->getName(),
            'display_name' => $conversation->getDisplayName(),
            'image_url' => $conversation->getImageUrl(),
            'properties' => $conversation->getProperties(),
        ];

        $data = $this->getApi()->update($conversation->getId(), $body);
        $conversation = $this->hydrator->hydrate($data);

        return $conversation;
    }
}
