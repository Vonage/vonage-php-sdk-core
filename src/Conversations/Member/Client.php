<?php

namespace Nexmo\Conversations\Member;

use Nexmo\User\User;
use Nexmo\Entity\Collection;
use Nexmo\Client\OpenAPIResource;
use Nexmo\Entity\FilterInterface;
use Nexmo\Client\ClientAwareTrait;
use Nexmo\Conversations\Conversation;
use Nexmo\Client\ClientAwareInterface;

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

    public function create(User $user, $action = 'invite') : Member
    {
        $body = [
            'channel' => ['type' => 'app'],
            'action' => $action
        ];

        if ($user->getId()) {
            $body['user_id'] = $user->getId();
        } elseif ($user->getName()) {
            $body['user_name'] = $user->getName();
        }

        if (!array_key_exists('user_id', $body) && !array_key_exists('user_name', $body)) {
            throw new \RuntimeException('Cannot add user to conversation, must supply user with name or ID');
        }

        $response = $this->getApi()->create($body);
        $member = $this->hydrator->hydrate($response);

        return $member;
    }

    public function delete(Member $member) : void
    {
        if (is_null($member->getId())) {
            throw new \RuntimeException('Member has no ID and cannot be deleted');
        }

        $this->getApi()->delete($member->getId());
    }

    public function get(string $id) : Member
    {
        $data = $this->getApi()->get($id);
        $member = $this->hydrator->hydrate($data);

        return $member;
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

    public function update(Member $member) : Member
    {
        if (is_null($member->getId())) {
            throw new \RuntimeException('Member has no ID and cannot be updated. Please create first.');
        }
        $body = [
            'state' => $member->getState(),
            'channel' => ['type' => 'app']
        ];

        $data = $this->getApi()->update($member->getId(), $body);
        $member = $this->hydrator->hydrate($data);

        return $member;
    }

    public function setConversation(Conversation $conversation)
    {
        $this->api->setBaseUri('/v0.1/conversations/' . $conversation->getId() . '/members');
        $this->api->setCollectionName('members');
    }
}
