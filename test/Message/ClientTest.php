<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Message;

use Nexmo\Client\Exception;
use Nexmo\Message\Client;
use Nexmo\Message\Message;
use Nexmo\Message\Shortcode\TwoFactor;
use Nexmo\Message\Text;
use NexmoTest\Psr7AssertionTrait;
use NexmoTest\MessageAssertionTrait;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;
    use MessageAssertionTrait;

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
        $this->nexmoClient->getRestUrl()->willReturn('https://rest.nexmo.com');
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

    public function testThrowConcurrentRequestsException()
    {
        try{
            $message = new Message('02000000D912945A');
            $response = $this->getResponse('empty', 429);

            $this->nexmoClient->send(Argument::that(function(Request $request) {
                $this->assertRequestQueryContains('id', '02000000D912945A', $request);
                return true;
            }))->willReturn($response);

            $this->messageClient->search($message);
            $this->fail('did not throw exception');
        } catch (\Nexmo\Client\Exception\Request $e) {
            $this->assertEquals('429', $e->getCode());
            $this->assertEquals('too many concurrent requests', $e->getMessage());
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
     * @dataProvider searchRejectionsProvider
     */
    public function testCanSearchRejections($date, $to, $responseFile, $expectedResponse, $expectedHttpCode, $expectedException)
    {
        $query = new \Nexmo\Message\Query($date, $to);

        $apiResponse = $this->getResponse($responseFile, $expectedHttpCode);

        $this->nexmoClient->send(Argument::that(function (Request $request) use ($to, $date) {
            $this->assertRequestQueryContains('to', $to, $request);
            $this->assertRequestQueryContains('date', $date->format('Y-m-d'), $request);
            return true;
        }))->willReturn($apiResponse);


        // If we're expecting this to throw an exception, listen for it in advance
        if ($expectedException !== null) {
                $this->expectException($expectedException);
                $this->expectExceptionMessage($expectedResponse);
        }

        // Make the request and assert that our responses match
        $rejectionsResponse = $this->messageClient->searchRejections($query);
        $this->assertListOfMessagesEqual($expectedResponse, $rejectionsResponse);
    }

    public function searchRejectionsProvider()
    {
        $r = [];

        $r['no rejections found'] = [new \DateTime(), '123456', 'search-rejections-empty', [], 200, null];

        // Build up our expected message object
        $message = new Message('0C0000005BA0B864');
        $message->setResponse($this->getResponse('search-rejections'));
        $message->setIndex(0);
        $r['rejection found'] = [new \DateTime(), '123456', 'search-rejections', [$message], 200, null];

        $r['error-code provided (validation)'] = [new \DateTime(), '123456', 'search-rejections-error-provided-validation', 'Validation error: You forgot to do something', 400, Exception\Request::class];
        $r['error-code provided (server error)'] = [new \DateTime(), '123456', 'search-rejections-error-provided-server-error', 'Gremlins! There are gremlins in the system!', 500, Exception\Request::class];
        $r['error-code not provided'] = [new \DateTime(), '123456', 'empty', 'error status from API', 500, Exception\Request::class];
        $r['missing items key in response on 200'] = [new \DateTime(), '123456', 'empty', 'unexpected response from API', 200, Exception\Exception::class];

        return $r;
    }

    public function testShortcodeWithObject()
    {
        $message = new TwoFactor('14155550100', [ 'link' => 'https://example.com' ], ['status-report-req' => 1]);

        $this->nexmoClient->send(Argument::that(function(Request $request) {
            $this->assertRequestJsonBodyContains('to', '14155550100', $request);
            $this->assertRequestJsonBodyContains('link', 'https://example.com', $request);
            $this->assertRequestJsonBodyContains('status-report-req', 1, $request);
            return true;
        }))->willReturn($this->getResponse('success-2fa'));

        $response = $this->messageClient->sendShortcode($message);
        $this->assertEquals([
            'message-count' => '1',
            'messages' =>[
                [
                    'status' => '0',
                    'message-id' => '00000123',
                    'to' => '14155550100',
                    'client-ref' => 'client-ref',
                    'remaining-balance' => '1.10',
                    'message-price' => '0.05',
                    'network' => '23410'
                ]
            ]
        ], $response);
    }

    public function testShortcodeError()
    {
        $args = [
            'to' => '14155550100',
            'custom' => [ 'link' => 'https://example.com' ],
            'options' => ['status-report-req' => 1],
            'type' => '2fa'
        ];

        $this->nexmoClient->send(Argument::that(function(Request $request) use ($args){
            return true;
        }))->willReturn($this->getResponse('error-2fa'));

        $this->expectException(Exception\Request::class);
        $this->expectExceptionMessage('Invalid Account for Campaign');

        $this->messageClient->sendShortcode($args);
    }

    public function testShortcodeWithArray()
    {
        $args = [
            'to' => '14155550100',
            'custom' => [ 'link' => 'https://example.com' ],
            'options' => ['status-report-req' => 1],
            'type' => '2fa'
        ];

        $this->nexmoClient->send(Argument::that(function(Request $request) use ($args){
            $this->assertRequestJsonBodyContains('to', $args['to'], $request);
            $this->assertRequestJsonBodyContains('link', $args['custom']['link'], $request);
            $this->assertRequestJsonBodyContains('status-report-req', $args['options']['status-report-req'], $request);
            return true;
        }))->willReturn($this->getResponse('success-2fa'));

        $response = $this->messageClient->sendShortcode($args);
        $this->assertEquals([
            'message-count' => '1',
            'messages' =>[
                [
                    'status' => '0',
                    'message-id' => '00000123',
                    'to' => '14155550100',
                    'client-ref' => 'client-ref',
                    'remaining-balance' => '1.10',
                    'message-price' => '0.05',
                    'network' => '23410'
                ]
            ]
        ], $response);
    }


    /**
     * Get the API response we'd expect for a call to the API. Message API currently returns 200 all the time, so only
     * change between success / fail is body of the message.
     *
     * @param string $type
     * @return Response
     */
    protected function getResponse($type = 'success', $code = 200)
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'r'), $code);
    }
}
