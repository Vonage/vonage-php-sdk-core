<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Conversations;

use Nexmo\Client;
use Nexmo\Conversations\Conversation;
use Nexmo\Conversations\Collection;
use NexmoTest\Psr7AssertionTrait;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Response;
use Nexmo\Client\Exception;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    use Psr7AssertionTrait;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $nexmoClient;

    /**
     * @var Collection
     */
    protected $collection;

    public function setUp()
    {
        $this->nexmoClient = $this->prophesize(Client::class);
        $this->nexmoClient->getApiUrl()->willReturn('https://api.nexmo.com');
        $this->collection = new Collection();
        $this->collection->setClient($this->nexmoClient->reveal());
    }

    /**
     * Getting an entity from the collection should not fetch it if we use the array interface.
     *
     * @dataProvider getConversation
     */
    public function testArrayIsLazy($payload, $id)
    {
        $this->nexmoClient->send(Argument::any())->willReturn($this->getResponse('conversation'));

        $conversation = $this->collection[$payload];

        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->nexmoClient->send(Argument::any())->shouldNotHaveBeenCalled();
        $this->assertEquals($id, $conversation->getId());

        if($payload instanceof Conversation){
            $this->assertSame($payload, $conversation);
        }

        // Once we call get() the rest of the data should be populated
        $conversation->get();
        $this->nexmoClient->send(Argument::any())->shouldHaveBeenCalled();
    }

    /**
     * Using `get()` should fetch the conversation data. Will accept both a string id and an object. Must return the same object
     * if that's the input.
     *
     * @dataProvider getConversation
     */
    public function testGetIsNotLazy($payload, $id)
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($id){
            $this->assertRequestUrl('api.nexmo.com', '/beta/conversations/' . $id, 'GET', $request);
            return true;
        }))->willReturn($this->getResponse('conversation'))->shouldBeCalled();

        $conversation = $this->collection->get($payload);

        $this->assertInstanceOf(Conversation::class, $conversation);
        if($payload instanceof Conversation){
            $this->assertSame($payload, $conversation);
        }
    }

    /**
     * @dataProvider postConversation
     */
    public function testCreatePostConversation($payload, $method)
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($payload){
            $this->assertRequestUrl('api.nexmo.com', '/beta/conversations', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('conversation', '200'));

        $conversation = $this->collection->$method($payload);

        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->assertEquals('CON-aaaaaaaa-bbbb-cccc-dddd-0123456789ab', $conversation->getId());
    }
    
    /**
     * @dataProvider postConversation
     */
    public function testCreatePostConversationErrorFromVApi($payload, $method)
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($payload){
            $this->assertRequestUrl('api.nexmo.com', '/beta/conversations', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('error_stitch', '400'));

        try {
            $this->collection->$method($payload);
            $this->fail('Expected to throw request exception');
        } catch (Exception\Request $e) {
            $this->assertEquals($e->getMessage(), 'the token was rejected');
        }
    }

    /**
     * @dataProvider postConversation
     */
    public function testCreatePostCallErrorFromProxy($payload, $method)
    {
        $this->markTestSkipped();
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($payload){
            $this->assertRequestUrl('api.nexmo.com', '/v1/conversation', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('error_proxy', '400'));

        try {
            $conversation = $this->collection->$method($payload);
            $this->fail('Expected to throw request exception');
        } catch (Exception\Request $e) {
            $this->assertEquals($e->getMessage(), 'Unsupported Media Type');
        }
    }

    /**
     * @dataProvider postConversation
     */
    public function testCreatePostCallErrorUnknownFormat($payload, $method)
    {
        $this->markTestSkipped();
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($payload){
            $this->assertRequestUrl('api.nexmo.com', '/v1/conversation', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('error_unknown_format', '400'));

        try {
            $conversation = $this->collection->$method($payload);
            $this->fail('Expected to throw request exception');
        } catch (Exception\Request $e) {
            $this->assertEquals($e->getMessage(), "Unexpected error");
        }
    }

    /**
     * Getting a conversation can use an object or an ID.
     *
     * @return array
     */
    public function getConversation()
    {
        return [
            ['3fd4d839-493e-4485-b2a5-ace527aacff3', '3fd4d839-493e-4485-b2a5-ace527aacff3'],
            [new Conversation('3fd4d839-493e-4485-b2a5-ace527aacff3'), '3fd4d839-493e-4485-b2a5-ace527aacff3']
        ];
    }

    /**
     * Creating a conversation can take a Call object or a simple array.
     * @return array
     */
    public function postConversation()
    {
        $raw = [
            'name' => 'demo',
            'display_name' => 'Demo Name'
        ];

        $conversation = new Conversation();
        $conversation->setName('demo')
             ->setDisplayName('Demo Name');

        return [
            [clone $conversation, 'create'],
            [clone $conversation, 'post'],
            [$raw, 'create'],
            [$raw, 'post'],
        ];
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

}
