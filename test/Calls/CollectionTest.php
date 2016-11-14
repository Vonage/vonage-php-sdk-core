<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Calls;

use Nexmo\Calls\Filter;
use Nexmo\Calls\Update\Transfer;
use Nexmo\Calls\Call;
use Nexmo\Calls\Collection;
use NexmoTest\Psr7AssertionTrait;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Response;

class CollectionTest extends \PHPUnit_Framework_TestCase
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
        $this->nexmoClient = $this->prophesize('Nexmo\Client');
        $this->collection = new Collection();
        $this->collection->setClient($this->nexmoClient->reveal());
    }

    /**
     * Getting an entity from the collection should not fetch it if we use the fluent interface.
     *
     * @dataProvider getCall
     */
    public function testArrayIsLazy($payload, $id)
    {
        $collection = $this->collection;
        $call = $collection[$payload];

        $this->assertInstanceOf('Nexmo\Calls\Call', $call);
        $this->nexmoClient->send()->shouldNotHaveBeenCalled();
        $this->assertEquals($id, $call->getId());

        if($payload instanceof Call){
            $this->assertSame($payload, $call);
        }

        $this->assertSame($this->collection, $call->getCollection());
    }

    public function testInvokeWithFilter()
    {
        $collection = $this->collection;
        $filter = new Filter();
        $return = $collection($filter);

        $this->assertSame($collection, $return);
        $this->assertSame($collection->getFilter(), $filter);
    }

    /**
     * Using `get()` should fetch the call data. Will accept both a string id and an object. Must return the same object
     * if that's the input.
     *
     * @dataProvider getCall
     */
    public function testGetIsNotLazy($payload, $id)
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($id){
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'GET', $request);
            return true;
        }))->willReturn($this->getResponse('call'));

        $call = $this->collection->get($payload);

        $this->assertInstanceOf('Nexmo\Calls\Call', $call);
        if($payload instanceof Call){
            $this->assertSame($payload, $call);
        }

        $this->assertSame($this->collection, $call->getCollection());
    }

    /**
     * @dataProvider postCall
     */
    public function testCreatPostCall($payload, $method)
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($payload){
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'POST', $request);
            $expected = json_decode(json_encode($payload), true);

            $request->getBody()->rewind();
            $body = json_decode($request->getBody()->getContents(), true);
            $request->getBody()->rewind();

            $this->assertEquals($expected, $body);
            return true;
        }))->willReturn($this->getResponse('created', '201'));

        $conversation = $this->collection->$method($payload);

        $this->assertInstanceOf('Nexmo\Conversations\Conversation', $conversation);
        $this->assertEquals('f9116360-cb27-4341-805c-b31af792833d', $conversation->getId());
    }

    /**
     * @dataProvider putCall
     */
    public function testPutCall($expectedId, $id, $payload)
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($expectedId, $payload){
            $this->assertEquals('/v1/calls/' . $expectedId, $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('PUT', $request->getMethod());

            $expected = json_decode(json_encode($payload), true);

            $request->getBody()->rewind();
            $body = json_decode($request->getBody()->getContents(), true);
            $request->getBody()->rewind();

            $this->assertEquals($expected, $body);

            return true;
        }))->willReturn($this->getResponse('updated'));

        $call = $this->collection->put($payload, $id);

        $this->assertInstanceOf('Nexmo\Calls\Call', $call);

        if($id instanceof Call){
            $this->assertSame($id, $call);
        } else {
            $this->assertEquals($id, $call->getId());
        }
    }

    /**
     * Getting a call can use an object or an ID.
     *
     * @return array
     */
    public function getCall()
    {
        return [
            ['3fd4d839-493e-4485-b2a5-ace527aacff3', '3fd4d839-493e-4485-b2a5-ace527aacff3'],
            [new Call('3fd4d839-493e-4485-b2a5-ace527aacff3'), '3fd4d839-493e-4485-b2a5-ace527aacff3']
        ];
    }

    /**
     * Creating a call can take a Call object or a simple array.
     * @return array
     */
    public function postCall()
    {
        $raw = [
            'to' => [[
                'type' => 'phone',
                'number' => '14843331234'
            ]],
            'from' => [
                'type' => 'phone',
                'number' => '14843335555'
            ],
            'answer_url' => ['https://example.com/answer'],
            'event_url' => ['https://example.com/event'],
            'answer_method' => 'POST',
            'event_method' => 'POST'
        ];


        $call = new Call();
        $call->setTo('14843331234')
             ->setFrom('14843335555')
             ->setWebhook(Call::WEBHOOK_ANSWER, 'https://example.com/answer', 'POST')
             ->setWebhook(Call::WEBHOOK_EVENT, 'https://example.com/event', 'POST');

        return [
            [clone $call, 'create'],
            [clone $call, 'post'],
            [$raw, 'create'],
            [$raw, 'post'],
        ];
    }

    /**
     * Can update the call with an object or a raw array.
     * @return array
     */
    public function putCall()
    {
        $id = '1234abcd';
        $payload = [
            'action' => 'transfer',
            'destination' => [
                'type' => 'ncco',
                'url' => ['http://example.com']
            ]
        ];

        $call = new Call($id);
        $transfer = new Transfer('http://example.com');

        return [
            [$id, $id, $payload],
            [$id, $call, $payload],
            [$id, $id, $transfer],
            [$id, $call, $transfer]
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
