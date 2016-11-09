<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Calls;

use Nexmo\Calls\Update\Transfer;
use Nexmo\Calls\Call;
use Nexmo\Calls\Client;
use NexmoTest\Psr7AssertionTrait;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Response;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    use Psr7AssertionTrait;

    protected $nexmoClient;

    /**
     * @var Client
     */
    protected $callClient;

    public function setUp()
    {
        $this->nexmoClient = $this->prophesize('Nexmo\Client');
        $this->callClient = new Client();
        $this->callClient->setClient($this->nexmoClient->reveal());
    }

    /**
     * @dataProvider updateCall
     */
    public function testUpdateCall($expectedId, $id, $payload)
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

        $call = $this->callClient->put($payload, $id);

        $this->assertInstanceOf('Nexmo\Calls\Call', $call);

        if($id instanceof Call){
            $this->assertSame($id, $call);
        } else {
            $this->assertEquals($id, $call->getId());
        }
    }

    public function updateCall()
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
     * @dataProvider getCall
     */
    public function testGetCall($payload, $id)
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($id){
            $this->assertEquals('/v1/calls/' . $id, $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('call'));

        $call = $this->callClient->get($payload);

        $this->assertInstanceOf('Nexmo\Calls\Call', $call);
        if($payload instanceof Call){
            $this->assertSame($payload, $call);
        }
    }

    public function getCall()
    {

        return [
            ['3fd4d839-493e-4485-b2a5-ace527aacff3', '3fd4d839-493e-4485-b2a5-ace527aacff3'],
            [new Call('3fd4d839-493e-4485-b2a5-ace527aacff3'), '3fd4d839-493e-4485-b2a5-ace527aacff3']
        ];
    }

    /**
     * @dataProvider createCall
     */
    public function testCreateCall($payload, $method)
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request){
            $this->assertEquals('/v1/calls', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());

            $this->assertRequestJsonBodyContains('to', [['type' => 'phone', 'number' => '14843331234']], $request);
            $this->assertRequestJsonBodyContains('from', ['type' => 'phone', 'number' => '14843335555'], $request);
            $this->assertRequestJsonBodyContains('answer_url', ['https://example.com/answer'], $request);
            $this->assertRequestJsonBodyContains('event_url' , ['https://example.com/event'] , $request);
            $this->assertRequestJsonBodyContains('answer_method', 'POST', $request);
            $this->assertRequestJsonBodyContains('event_method' , 'POST', $request);
            return true;
        }))->willReturn($this->getResponse('created', '201'));

        $conversation = $this->callClient->$method($payload);

        //is an application object was provided, should be the same
        $this->assertInstanceOf('Nexmo\Conversations\Conversation', $conversation);
        $this->assertEquals('f9116360-cb27-4341-805c-b31af792833d', $conversation->getId());
    }

    public function createCall()
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
