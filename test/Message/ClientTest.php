<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Message;

use Nexmo\Message\Client;
use Nexmo\Message\Message;
use Nexmo\Message\Text;
use NexmoTest\Psr7AssertionTrait;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    use Psr7AssertionTrait;

    protected $nexmoClient;

    /**
     * @var Client
     */
    protected $messageClient;
    /**
     * Create the Message API Client, and mock the Nexmo Client
     */
    public function setUp()
    {
        $this->nexmoClient = $this->prophesize('Nexmo\Client');
        $this->messageClient = new Client();
        $this->messageClient->setClient($this->nexmoClient->reveal());
    }

    public function testCanUseMessage()
    {
        $args = [
            'to' => '14845551212',
            'from' => '16105551212',
            'text' => 'Go To Gino\'s'
        ];

        $this->nexmoClient->send(Argument::that(function(Request $request) use ($args){
            $this->assertRequestJsonBodyContains('to', $args['to'], $request);
            $this->assertRequestJsonBodyContains('from', $args['from'], $request);
            $this->assertRequestJsonBodyContains('text', $args['text'], $request);
            return true;
        }))->willReturn($this->getResponse());

        $message = $this->messageClient->send(new Text($args['to'], $args['from'], $args['text']));
        $this->assertInstanceOf('Nexmo\Message\Text', $message);
    }

    public function  testCanUseArguments()
    {
        $args = [
            'to' => '14845551212',
            'from' => '16105551212',
            'text' => 'Go To Gino\'s'
        ];

        $this->nexmoClient->send(Argument::that(function(Request $request) use ($args){
            $this->assertRequestJsonBodyContains('to', $args['to'], $request);
            $this->assertRequestJsonBodyContains('from', $args['from'], $request);
            $this->assertRequestJsonBodyContains('text', $args['text'], $request);
            return true;
        }))->willReturn($this->getResponse());

        $message = $this->messageClient->send($args);
        $this->assertInstanceOf('Nexmo\Message\Message', $message);
    }

    public function testSentMessageHasResponse()
    {
        $response = $this->getResponse();
        $this->nexmoClient->send(Argument::type(RequestInterface::class))->willReturn($response);

        $message = $this->messageClient->send(new Text('14845551212', '16105551212', 'Not Pats?'));
        $this->assertSame($response, $message->getResponse());
        $this->nexmoClient->send($message->getRequest())->shouldHaveBeenCalled();
    }

    public function testThrowRequestException()
    {
        $response = $this->getResponse('fail');
        $this->nexmoClient->send(Argument::type(RequestInterface::class))->willReturn($response);
        $message = new Text('14845551212', '16105551212', 'Not Pats?');

        try{
            $this->messageClient->send($message);
            $this->fail('did not throw exception');
        } catch (\Nexmo\Client\Exception\Request $e) {
            $this->assertSame($message, $e->getEntity());
            $this->assertEquals('2', $e->getCode());
            $this->assertEquals('Missing from param', $e->getMessage());
        }
    }

    public function testThrowServerException()
    {
        $response = $this->getResponse('fail-server');
        $this->nexmoClient->send(Argument::type(RequestInterface::class))->willReturn($response);
        $message = new Text('14845551212', '16105551212', 'Not Pats?');

        try{
            $this->messageClient->send($message);
            $this->fail('did not throw exception');
        } catch (\Nexmo\Client\Exception\Server $e) {
            $this->assertEquals('5', $e->getCode());
            $this->assertEquals('Server Error', $e->getMessage());
        }
    }

    public function testCanSearchByMessage()
    {
        $message = new Message('02000000D912945A');
        $response = $this->getResponse('search-outbound');

        $this->nexmoClient->send(Argument::that(function(Request $request) {
            $this->assertRequestQueryContains('id', '02000000D912945A', $request);
            return true;
        }))->willReturn($response);

        $this->messageClient->search($message);
        $this->assertSame($response, $message->getResponse());
    }

    public function testCanSearchBySingleOutboundId()
    {
        $response = $this->getResponse('search-outbound');

        $this->nexmoClient->send(Argument::that(function(Request $request) {
            $this->assertRequestQueryContains('id', '02000000D912945A', $request);
            return true;
        }))->willReturn($response);

        $message = $this->messageClient->search('02000000D912945A');

        $this->assertInstanceOf('Nexmo\Message\Message', $message);
        $this->assertSame($response, $message->getResponse());
    }

    public function testCanSearchBySingleInboundId()
    {
        $response = $this->getResponse('search-inbound');

        $this->nexmoClient->send(Argument::that(function(Request $request) {
            $this->assertRequestQueryContains('id', '02000000DA7C52E7', $request);
            return true;
        }))->willReturn($response);

        $message = $this->messageClient->search('02000000DA7C52E7');

        $this->assertInstanceOf('Nexmo\Message\InboundMessage', $message);
        $this->assertSame($response, $message->getResponse());
    }

    public function testRateLimitRetires()
    {
        $rate    = $this->getResponse('ratelimit');
        $success = $this->getResponse('success');

        $args = [
            'to' => '14845551345',
            'from' => '1105551334',
            'text' => 'test message'
        ];

        $this->nexmoClient->send(Argument::that(function(Request $request) use ($args){
            $this->assertRequestJsonBodyContains('to', $args['to'], $request);
            $this->assertRequestJsonBodyContains('from', $args['from'], $request);
            $this->assertRequestJsonBodyContains('text', $args['text'], $request);
            return true;
        }))->willReturn($rate, $rate, $success);

        $message = $this->messageClient->send(new Text($args['to'], $args['from'], $args['text']));
        $this->assertEquals($success, $message->getResponse());
    }

    /**
     * Get the API response we'd expect for a call to the API. Message API currently returns 200 all the time, so only
     * change between success / fail is body of the message.
     *
     * @param string $type
     * @return Response
     */
    protected function getResponse($type = 'success')
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'r'));
    }
}
