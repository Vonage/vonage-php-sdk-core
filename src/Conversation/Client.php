<?php

namespace Vonage\Conversation;

use Vonage\Client\APIResource;
use Vonage\Conversation\ConversationObjects\EventRequest;
use Vonage\Conversation\ConversationObjects\Conversation;
use Vonage\Conversation\ConversationObjects\CreateConversationRequest;
use Vonage\Conversation\ConversationObjects\CreateMemberRequest;
use Vonage\Conversation\ConversationObjects\Event;
use Vonage\Conversation\ConversationObjects\Member;
use Vonage\Conversation\ConversationObjects\UpdateConversationRequest;
use Vonage\Conversation\ConversationObjects\UpdateMemberRequest;
use Vonage\Conversation\Filter\ListConversationFilter;
use Vonage\Conversation\Filter\ListEventsFilter;
use Vonage\Conversation\Filter\ListMembersFilter;
use Vonage\Conversation\Filter\ListUserConversationsFilter;
use Vonage\Entity\Hydrator\ArrayHydrator;
use Vonage\Entity\IterableAPICollection;

class Client
{
    public function __construct(protected APIResource $api)
    {
    }

    public function listConversations(
        ?ListConversationFilter $conversationFilter = null
    ): IterableAPICollection {
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

    public function createConversation(CreateConversationRequest $createConversation): Conversation
    {
        $response = $this->api->create($createConversation->toArray());
        $conversation = new Conversation();
        $conversation->fromArray($response);

        return $conversation;
    }

    public function getConversationById(string $id): Conversation
    {
        $response = $this->api->get($id);
        $conversation = new Conversation();
        $conversation->fromArray($response);

        return $conversation;
    }

    public function updateConversationById(string $id, UpdateConversationRequest $updateRequest): Conversation
    {
        $response = $this->api->update($id, $updateRequest->toArray());
        $conversation = new Conversation();
        $conversation->fromArray($response);

        return $conversation;
    }

    public function deleteConversationById(string $id): bool
    {
        $this->api->delete($id);

        return true;
    }

    public function listUserConversationsByUserId(
        string $userId,
        ?ListUserConversationsFilter $filter = null
    ): IterableAPICollection {
        $api = clone $this->api;
        $api->setBaseUrl('https://api.nexmo.com/v1/users');
        $response = $api->search($filter, '/' . $userId . '/conversations');
        $response->setHasPagination(true);
        $response->setNaiveCount(true);

        $hydrator = new ArrayHydrator();
        $hydrator->setPrototype(new Conversation());
        $response->setHydrator($hydrator);

        return $response;
    }

    public function listMembersByConversationId(
        string $conversationId,
        ?ListMembersFilter $filter = null
    ): IterableAPICollection {
        $api = clone $this->api;
        $api->setBaseUrl('https://api.nexmo.com/v1/users/');
        $api->setCollectionName('members');
        $response = $api->search($filter, $conversationId . '/members');
        $response->setHasPagination(true);
        $response->setNaiveCount(true);

        $hydrator = new ArrayHydrator();
        $hydrator->setPrototype(new Member());
        $response->setHydrator($hydrator);

        return $response;
    }

    public function createMember(CreateMemberRequest $createMemberRequest, string $conversationId): ?array
    {
        return $this->api->create($createMemberRequest->toArray(), '/' . $conversationId . '/members');
    }

    public function getMyMemberByConversationId(string $id): Member
    {
        $response = $this->api->get($id . '/members/me');
        $member = new Member();
        $member->fromArray($response);

        return $member;
    }

    public function getMemberByConversationId(string $memberId, string $conversationId): Member
    {
        $response = $this->api->get($conversationId . '/members/' . $memberId);
        $member = new Member();
        $member->fromArray($response);

        return $member;
    }

    public function updateMember(UpdateMemberRequest $updateMemberRequest): Member
    {
        $response = $this->api->update(
            $updateMemberRequest->getConversationId() . '/members/' . $updateMemberRequest->getMemberId(),
            $updateMemberRequest->toArray()
        );

        $member = new Member();
        $member->fromArray($response);

        return $member;
    }

    public function deleteMember(string $memberId, string $conversationId): bool
    {
        $this->api->delete($conversationId . '/members/' . $memberId);

        return true;
    }

    public function createEvent(EventRequest $event): Event
    {
        $response = $this->api->create($event->toArray(), '/' . $event->getConversationId() . '/events');

        $member = new Event();
        $member->fromArray($response);

        return $member;
    }

    public function listEvents(string $conversationId, ListEventsFilter $filter): IterableAPICollection
    {
        $response = $this->api->search($filter, '/' . $conversationId . '/events');
        $response->setHasPagination(false);
        $response->setNaiveCount(true);
        $response->setHalNoCollection(true);

        return $response;
    }

    public function getEventById(string $eventId, string $conversationId): Event
    {
        $response = $this->api->get($conversationId . '/events/' . $eventId);
        $member = new Event();
        $member->fromArray($response);

        return $member;
    }

    public function deleteEventById(string $eventId, $conversationId): bool
    {
        $this->api->delete($conversationId . '/events/' . $eventId);

        return true;
    }
}
