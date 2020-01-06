<?php

namespace NexmoTest\Conversations;

use Nexmo\Client\OpenAPIResource;
use Nexmo\User\User;
use Prophecy\Prophet;
use Prophecy\Argument;
use Zend\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use NexmoTest\Psr7AssertionTrait;
use Nexmo\Conversations\Event\API;
use Nexmo\Conversations\Event\Event;
use Nexmo\Conversations\Conversation;
use Nexmo\Conversations\Event\Client;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\RequestInterface;
use Nexmo\Conversations\Event\Hydrator;
use Nexmo\Conversations\Member\API as MemberAPI;
use Nexmo\Conversations\Member\Client as MemberClient;
use Nexmo\Conversations\Member\Hydrator as MemberHydrator;
use Nexmo\Conversations\Member\Member;
use Nexmo\Entity\EmptyFilter;

class ConversationTest extends TestCase
{
    use Psr7AssertionTrait;

    /**
     * @var API
     */
    protected $apiClient;

    /**
     * @var Conversation
     */
    protected $conversation;

    /**
     * @var API
     */
    protected $eventAPI;

    /**
     * @var Client
     */
    protected $eventClient;

    /**
     * @var MemberAPI
     */
    protected $memberAPI;

    /**
     * @var MemberClient
     */
    protected $memberClient;

    protected $nexmoClient;

    protected $prophet;

    public function setUp()
    {
        $this->nexmoClient = $this->prophesize('Nexmo\Client');
        $this->nexmoClient->getApiUrl()->willReturn('http://api.nexmo.com');

        $this->eventAPI = new OpenAPIResource();
        $this->eventAPI->setClient($this->nexmoClient->reveal());

        $this->memberAPI = new OpenAPIResource();
        $this->memberAPI->setClient($this->nexmoClient->reveal());

        $this->eventClient = new Client($this->eventAPI, new Hydrator());
        $this->memberClient = new MemberClient($this->memberAPI, new MemberHydrator());

        $this->conversation = new Conversation();
        $this->conversation->createFromArray(json_decode(file_get_contents(__DIR__ . '/responses/conversation.json'), true));
        $this->conversation->setEventClient($this->eventClient);
        $this->conversation->setMemberClient($this->memberClient);
    }

    private function getProphet(): Prophet
    {
        if ($this->prophet === null) {
            $this->prophet = new Prophet();
        }

        return $this->prophet;
    }

    /**
     * @param null|string $classOrInterface
     *
     * @throws Prophecy\Exception\Doubler\ClassNotFoundException
     * @throws Prophecy\Exception\Doubler\DoubleException
     * @throws Prophecy\Exception\Doubler\InterfaceNotFoundException
     */
    protected function prophesize($classOrInterface = null): ObjectProphecy
    {
        return $this->getProphet()->prophesize($classOrInterface);
    }

    /**
     * Get the API response we'd expect for a call to the API.
     *
     * @param string $type
     * @return Response
     */
    protected function getResponse($type = 'success', $status = 200)
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'r'), $status);
    }

    public function testAddEventToConversation()
    {
        $expected = [
            'type' => 'text',
            'body' => ['text' => 'Hello World'],
            'from' => 'MEM-afe887d8-d587-4280-9aae-dfa4c9227d5e',
            'to' => 'MEM-afe887d8-d587-4280-9aae-dfa4c9227d5c',
        ];

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($expected) {
            $this->assertEquals('/v0.1/conversations/CON-afe887d8-d587-4280-9aae-dfa4c9227d5e/events', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('event'));

        $event = new Event();
        $event->createFromArray($expected);

        $event = $this->conversation->addEvent($event);

        $this->assertSame($expected['type'], $event->getType());
        $this->assertSame($expected['body'], $event->getBody());
        $this->assertSame($expected['from'], $event->getFrom());
    }

    public function testAddMemberToConversation()
    {
        $expected = [
            'user_id' => 'USR-2c52f0ec-7a48-4b52-9d47-df47482b2b7e',
            'channel' => ['type' => 'app'],
            'action' => 'invite',
        ];

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($expected) {
            $this->assertEquals('/v0.1/conversations/CON-afe887d8-d587-4280-9aae-dfa4c9227d5e/members', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestBodyIsJson(json_encode($expected), $request);
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('member'));

        $user = new User($expected['user_id']);
        $user = $this->conversation->addMember($user);

        $this->assertSame($expected['user_id'], $user->getUserId());
        $this->assertSame('MEM-afe887d8-d587-4280-9aae-dfa4c9227d5e', $user->getId());
        $this->assertSame($this->conversation->getId(), $user->getConversationId());
    }

    public function testDeleteMemberFromConversation()
    {
        $memberID = 'MEM-afe887d8-d587-4280-9aae-dfa4c9227d5e';

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($memberID) {
            $this->assertEquals('/v0.1/conversations/CON-afe887d8-d587-4280-9aae-dfa4c9227d5e/members/' . $memberID, $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('DELETE', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('empty', 204));

        $member = new Member();
        $member->setId($memberID);
        $this->conversation->deleteMember($member);
    }

    public function testUpdateMemberInConversation()
    {
        $memberID = 'MEM-afe887d8-d587-4280-9aae-dfa4c9227d5e';

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($memberID) {
            $this->assertEquals('/v0.1/conversations/CON-afe887d8-d587-4280-9aae-dfa4c9227d5e/members/' . $memberID, $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('PUT', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('member-changed', 200));

        $member = new Member();
        $member
            ->setId($memberID)
            ->setName('bob')
            ->setDisplayName('Bob Smith')
        ;

        $member = $this->conversation->updateMember($member);

        $this->assertSame('bob', $member->getName());
        $this->assertSame('Bob Smith', $member->getDisplayName());
        $this->assertSame($memberID, $member->getId());
    }

    public function testJoinMemberToConversation()
    {
        $memberID = 'MEM-afe887d8-d587-4280-9aae-dfa4c9227d5e';

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($memberID) {
            $this->assertEquals('/v0.1/conversations/CON-afe887d8-d587-4280-9aae-dfa4c9227d5e/members/' . $memberID, $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('PUT', $request->getMethod());
            
            $expected = ['state' => 'join', 'channel' => ['type' => 'app']];
            $this->assertRequestBodyIsJson(json_encode($expected), $request);
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('member-changed', 200));

        $member = new Member();
        $member->setId($memberID);
        $member = $this->conversation->joinMember($member);

        $this->assertSame('JOINED', $member->getState());
    }

    public function testDeleteEventFromConversation()
    {
        $eventID = 9;

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($eventID) {
            $this->assertEquals('/v0.1/conversations/CON-afe887d8-d587-4280-9aae-dfa4c9227d5e/events/' . $eventID, $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('DELETE', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('empty', 204));

        $event = new Event();
        $event->setId($eventID);
        $this->conversation->deleteEvent($event);
    }

    public function testConvertsToArray()
    {
        $data = $this->conversation->toArray();

        $this->assertSame($data['id'], $this->conversation->getId());
        $this->assertSame($data['name'], $this->conversation->getName());
        $this->assertSame($data['display_name'], $this->conversation->getDisplayName());
        $this->assertSame($data['image_url'], $this->conversation->getImageUrl());
        $this->assertSame($data['timestamp']['created'], $this->conversation->getTimestamp()->format(\DateTime::RFC3339));
    }

    public function testGetEventReturnsCorrectEvent()
    {
        $eventID = 9;
        $expected = json_decode(file_get_contents(__DIR__ . '/responses/event.json'), true);

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($eventID) {
            $this->assertEquals('/v0.1/conversations/CON-afe887d8-d587-4280-9aae-dfa4c9227d5e/events/' . $eventID, $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('event'));

        $event = $this->conversation->getEvent($eventID);

        $this->assertSame($expected['type'], $event->getType());
        $this->assertSame($expected['body'], $event->getBody());
        $this->assertSame($expected['from'], $event->getFrom());
    }

    public function testGetMemberReturnsCorrectMember()
    {
        $memberID = 'MEM-afe887d8-d587-4280-9aae-dfa4c9227d5e';
        $expected = json_decode(file_get_contents(__DIR__ . '/responses/member.json'), true);

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($memberID) {
            $this->assertEquals('/v0.1/conversations/CON-afe887d8-d587-4280-9aae-dfa4c9227d5e/members/' . $memberID, $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('member'));

        $member = $this->conversation->getMember($memberID);

        $this->assertSame($expected['id'], $member->getId());
        $this->assertSame($expected['name'], $member->getName());
        $this->assertSame($expected['user_id'], $member->getUserId());
    }

    public function testGetEventsViaSearch()
    {
        $expected = json_decode(file_get_contents(__DIR__ . '/responses/events-list.json'), true);

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/v0.1/conversations/CON-afe887d8-d587-4280-9aae-dfa4c9227d5e/events', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('events-list'));

        $events = $this->conversation->searchEvents();
        $event = $events->current();

        $this->assertSame($expected['_embedded']['events'][0]['type'], $event->getType());
        $this->assertSame($expected['_embedded']['events'][0]['body'], $event->getBody());
        $this->assertSame($expected['_embedded']['events'][0]['from'], $event->getFrom());
    }

    public function testGetMembersViaSearch()
    {
        $expected = json_decode(file_get_contents(__DIR__ . '/responses/members-list.json'), true);

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/v0.1/conversations/CON-afe887d8-d587-4280-9aae-dfa4c9227d5e/members', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('members-list'));

        $members = $this->conversation->searchMembers();
        $member = $members->current();

        $this->assertSame($expected['_embedded']['members'][0]['id'], $member->getId());
        $this->assertSame($expected['_embedded']['members'][0]['name'], $member->getName());
        $this->assertSame($expected['_embedded']['members'][0]['user_id'], $member->getUserId());
    }

    public function testCanSetAndRetrieveProperties()
    {
        $properties = [
            'key' => 'value',
        ];

        $this->conversation->setProperties($properties);
        $this->assertSame($properties, $this->conversation->getProperties());

        $this->conversation->setProperty('foo', 'bar');
        $this->assertSame('bar', $this->conversation->getProperty('foo'));

        // Unknown properties just return null
        $this->assertSame(null, $this->conversation->getProperty('baz'));
    }
}
