<?php

namespace NexmoTest\Conversations;

use Nexmo\Client\OpenAPIResource;
use Nexmo\Conversations\Client;
use Nexmo\Conversations\Conversation;
use Nexmo\Conversations\Event\API;
use Nexmo\Conversations\Event\Client as EventClient;
use Nexmo\Conversations\Event\Hydrator as EventHydrator;
use Nexmo\Conversations\Hydrator;
use Nexmo\Conversations\Member\Client as MemberClient;
use Nexmo\Conversations\Member\Hydrator as MemberHydrator;
use NexmoTest\Psr7AssertionTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use Prophecy\Argument;
use Zend\Diactoros\Response;
use Psr\Http\Message\RequestInterface;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    /**
     * @var API
     */
    protected $apiClient;

    /**
     * @var Client
     */
    protected $conversationClient;

    protected $nexmoClient;

    protected $prophet;

    public function setUp()
    {
        $this->nexmoClient = $this->prophesize('Nexmo\Client');
        $this->nexmoClient->getApiUrl()->willReturn('http://api.nexmo.com');

        $this->apiClient = new OpenAPIResource();
        $this->apiClient->setBaseUri('/v0.1/conversations');
        $this->apiClient->setCollectionName('conversations');
        $this->apiClient->setClient($this->nexmoClient->reveal());

        $this->eventClient = new EventClient(new OpenAPIResource(), new EventHydrator());
        $this->memberClient = new MemberClient(new OpenAPIResource(), new MemberHydrator());
        $this->conversationClient = new Client($this->apiClient, new Hydrator($this->eventClient, $this->memberClient));
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

    public function testFetchConversation()
    {
        $expected = [
            'id' => 'CON-afe887d8-d587-4280-9aae-dfa4c9227d5e',
            'name' => 'my-conversation',
            'display_name' => 'Conversation with Ashley',
            'image_url' => 'https://example.com/my-image.png',
            'timestamp' => new \DateTimeImmutable('2019-09-03T18:40:24.324Z')
        ];

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($expected) {
            $this->assertEquals('/v0.1/conversations/' . $expected['id'], $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('conversation'));

        $conversation = $this->conversationClient->get('CON-afe887d8-d587-4280-9aae-dfa4c9227d5e');

        $this->assertSame($expected['id'], $conversation->getId());
        $this->assertSame($expected['name'], $conversation->getName());
        $this->assertSame($expected['display_name'], $conversation->getDisplayName());
        $this->assertSame($expected['image_url'], $conversation->getImageUrl());
        $this->assertEquals($expected['timestamp'], $conversation->getTimestamp());
    }

    public function testSearchConversations()
    {
        $expected = [
            'id' => 'CON-afe887d8-d587-4280-9aae-dfa4c9227d5e',
            'name' => 'my-conversation',
            'display_name' => 'Conversation with Ashley',
            'image_url' => 'https://example.com/my-image.png',
            'timestamp' => new \DateTimeImmutable('2019-09-03T18:40:24.324Z')
        ];

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($expected) {
            $this->assertEquals('/v0.1/conversations', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('conversations-list'));

        $collection = $this->conversationClient->search();
        $conversation = $collection->current();

        $this->assertSame($expected['id'], $conversation->getId());
        $this->assertSame($expected['name'], $conversation->getName());
        $this->assertSame($expected['display_name'], $conversation->getDisplayName());
        $this->assertSame($expected['image_url'], $conversation->getImageUrl());
        $this->assertEquals($expected['timestamp'], $conversation->getTimestamp());
    }

    public function testDeleteConversation()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals(
                '/v0.1/conversations/CON-afe887d8-d587-4280-9aae-dfa4c9227d5e',
                $request->getUri()->getPath()
            );
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('DELETE', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('empty', 204));

        $conversation = new Conversation();
        $conversation->createFromArray([
            'id' => 'CON-afe887d8-d587-4280-9aae-dfa4c9227d5e',
            'name' => 'my-conversation',
            'display_name' => 'Conversation with Ashley',
            'image_url' => 'https://example.com/my-image.png',
            'timestamp' => ['created' => '2019-09-03T18:40:24.324Z']
        ]);

        $this->conversationClient->delete($conversation);
    }

    public function testUpdateConversation()
    {
        $expected = [
            'id' => 'CON-afe887d8-d587-4280-9aae-dfa4c9227d5e',
            'name' => 'my-conversation',
            'display_name' => 'Conversation with Ashley',
            'image_url' => 'https://example.com/my-image.png',
        ];

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($expected) {
            $this->assertEquals('/v0.1/conversations/' . $expected['id'], $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('PUT', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('conversation'));

        $conversation = new Conversation();
        $conversation->createFromArray($expected);
        $conversation = $this->conversationClient->update($conversation);

        $this->assertSame($expected['id'], $conversation->getId());
        $this->assertSame($expected['name'], $conversation->getName());
        $this->assertSame($expected['display_name'], $conversation->getDisplayName());
        $this->assertSame($expected['image_url'], $conversation->getImageUrl());
    }

    public function testCreateConversation()
    {
        $expected = [
            'id' => 'CON-afe887d8-d587-4280-9aae-dfa4c9227d5e',
            'name' => 'my-conversation',
            'display_name' => 'Conversation with Ashley',
            'image_url' => 'https://example.com/my-image.png',
        ];

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($expected) {
            $this->assertEquals('/v0.1/conversations', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('conversation'));

        $conversation = new Conversation();
        $conversation->createFromArray($expected);
        $conversation = $this->conversationClient->create($conversation);

        $this->assertSame($expected['id'], $conversation->getId());
        $this->assertSame($expected['name'], $conversation->getName());
        $this->assertSame($expected['display_name'], $conversation->getDisplayName());
        $this->assertSame($expected['image_url'], $conversation->getImageUrl());
    }
}
