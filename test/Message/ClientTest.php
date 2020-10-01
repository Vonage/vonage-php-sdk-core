<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Message;

use Prophecy\Argument;
use Vonage\Message\Text;
use Vonage\Message\Query;
use Vonage\Message\Client;
use Vonage\Message\Message;
use Vonage\Client\Exception;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use Vonage\Message\InboundMessage;
use VonageTest\Psr7AssertionTrait;
use VonageTest\MessageAssertionTrait;
use Vonage\Message\Shortcode\TwoFactor;
use Psr\Http\Message\RequestInterface;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;
    use MessageAssertionTrait;

    protected $vonageClient;

    /**
     * @var Client
     */
    protected $messageClient;
    
    /**
     * Create the Message API Client, and mock the Vonage Client
     */
    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize('Vonage\Client');
        $this->vonageClient->getRestUrl()->willReturn('https://rest.nexmo.com');
        $this->messageClient = new Client();
        $this->messageClient->setClient($this->vonageClient->reveal());
    }

    public function testCanUseMessage()
    {
        $args = [
            'to' => '14845551212',
            'from' => '16105551212',
            'text' => 'Go To Gino\'s'
        ];

        $this->vonageClient->send(Argument::that(function (Request $request) use ($args){
            $this->assertRequestJsonBodyContains('to', $args['to'], $request);
            $this->assertRequestJsonBodyContains('from', $args['from'], $request);
            $this->assertRequestJsonBodyContains('text', $args['text'], $request);
            return true;
        }))->willReturn($this->getResponse());

        $message = $this->messageClient->send(new Text($args['to'], $args['from'], $args['text']));
        $this->assertInstanceOf('Vonage\Message\Text', $message);
    }

    public function testThrowsRequestExceptionWhenInvalidAPIResponse()
    {
        $this->expectException('\Vonage\Client\Exception\Request');
        $this->expectExceptionMessage('unexpected response from API');

        $args = [
            'to' => '14845551212',
            'from' => '16105551212',
            'text' => 'Go To Gino\'s'
        ];

        $this->vonageClient->send(Argument::that(function (Request $request) use ($args){
            $this->assertRequestJsonBodyContains('to', $args['to'], $request);
            $this->assertRequestJsonBodyContains('from', $args['from'], $request);
            $this->assertRequestJsonBodyContains('text', $args['text'], $request);
            return true;
        }))->willReturn($this->getResponse('empty'));

        $message = $this->messageClient->send(new Text($args['to'], $args['from'], $args['text']));
    }

    public function testCanUseArguments()
    {
        $args = [
            'to' => '14845551212',
            'from' => '16105551212',
            'text' => 'Go To Gino\'s'
        ];

        $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
            $this->assertRequestJsonBodyContains('to', $args['to'], $request);
            $this->assertRequestJsonBodyContains('from', $args['from'], $request);
            $this->assertRequestJsonBodyContains('text', $args['text'], $request);
            return true;
        }))->willReturn($this->getResponse());

        @$message = $this->messageClient->send($args);
        $this->assertInstanceOf('Vonage\Message\Message', $message);
    }

    public function testSentMessageHasResponse()
    {
        $response = $this->getResponse();
        $this->vonageClient->send(Argument::type(RequestInterface::class))->willReturn($response);

        $message = $this->messageClient->send(new Text('14845551212', '16105551212', 'Not Pats?'));
        $this->assertSame($response, @$message->getResponse());
        $this->vonageClient->send(@$message->getRequest())->shouldHaveBeenCalled();
    }

    public function testThrowRequestException()
    {
        $response = $this->getResponse('fail');
        $this->vonageClient->send(Argument::type(RequestInterface::class))->willReturn($response);
        $message = new Text('14845551212', '16105551212', 'Not Pats?');

        try {
            $this->messageClient->send($message);
            $this->fail('did not throw exception');
        } catch (\Vonage\Client\Exception\Request $e) {
            $this->assertSame($message, $e->getEntity());
            $this->assertEquals('2', $e->getCode());
            $this->assertEquals('Missing from param', $e->getMessage());
        }
    }

    public function testThrowServerException()
    {
        $response = $this->getResponse('fail-server');
        $this->vonageClient->send(Argument::type(RequestInterface::class))->willReturn($response);
        $message = new Text('14845551212', '16105551212', 'Not Pats?');

        try{
            $this->messageClient->send($message);
            $this->fail('did not throw exception');
        } catch (\Vonage\Client\Exception\Server $e) {
            $this->assertEquals('5', $e->getCode());
            $this->assertEquals('Server Error', $e->getMessage());
        }
    }

    public function testThrowConcurrentRequestsException()
    {
        try {
            $message = new Message('02000000D912945A');
            $response = $this->getResponse('empty', 429);

            $this->vonageClient->send(Argument::that(function (Request $request) {
                $this->assertRequestQueryContains('id', '02000000D912945A', $request);
                return true;
            }))->willReturn($response);

            $this->messageClient->search($message);
            $this->fail('did not throw exception');
        } catch (\Vonage\Client\Exception\Request $e) {
            $this->assertEquals('429', $e->getCode());
            $this->assertEquals('too many concurrent requests', $e->getMessage());
        }
    }

    public function testCanGetMessageWithMessageObject()
    {
        $message = new Message('02000000D912945A');
        $response = $this->getResponse('get-outbound');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);
            return true;
        }))->willReturn($response);

        $messages = $this->messageClient->get($message);
        $body = json_decode($response->getBody(), true);

        $this->assertSame($body['count'], count($messages));
        $this->assertSame($body['items'][0]['message-id'], $messages[0]->getMessageId());
    }

    public function testCanGetInboundMessage()
    {
        $message = new Message('0B00000053FFB40F');
        $response = $this->getResponse('get-inbound');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('ids', ['0B00000053FFB40F'], $request);
            return true;
        }))->willReturn($response);

        $messages = $this->messageClient->get($message);
        $body = json_decode($response->getBody(), true);

        $this->assertSame($body['count'], count($messages));
        $this->assertSame($body['items'][0]['message-id'], $messages[0]->getMessageId());
        $this->assertTrue($messages[0] instanceof InboundMessage);
    }

    public function testGetThrowsExceptionOnBadMessageType()
    {
        $this->expectException(Exception\Request::class);
        $this->expectExceptionMessage('unexpected response from API');

        $message = new Message('0B00000053FFB40F');
        $response = $this->getResponse('get-invalid-type');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('ids', ['0B00000053FFB40F'], $request);
            return true;
        }))->willReturn($response);

        $this->messageClient->get($message);
    }

    public function testGetReturnsEmptyArrayWithNoResults()
    {
        $message = new Message('02000000D912945A');
        $response = $this->getResponse('get-no-results');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);
            return true;
        }))->willReturn($response);

        $messages = $this->messageClient->get($message);
        $body = json_decode($response->getBody(), true);

        $this->assertSame(0, count($messages));
    }

    public function testCanGetMessageWithStringID()
    {
        $messageID = '02000000D912945A';
        $response = $this->getResponse('get-outbound');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);
            return true;
        }))->willReturn($response);

        $messages = $this->messageClient->get($messageID);
        $body = json_decode($response->getBody(), true);

        $this->assertSame($body['count'], count($messages));
        $this->assertSame($body['items'][0]['message-id'], $messages[0]->getMessageId());
    }

    public function testCanGetMessageWithArrayOfIDs()
    {
        $messageIDs = ['02000000D912945A'];
        $response = $this->getResponse('get-outbound');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);
            return true;
        }))->willReturn($response);

        $messages = $this->messageClient->get($messageIDs);
        $body = json_decode($response->getBody(), true);

        $this->assertSame($body['count'], count($messages));
        $this->assertSame($body['items'][0]['message-id'], $messages[0]->getMessageId());
    }

    public function testCanGetMessageWithQuery()
    {
        $query = new Query(new \DateTime('2016-05-19'), '14845551212');
        $response = $this->getResponse('get-outbound');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('date', '2016-05-19', $request);
            $this->assertRequestQueryContains('to', '14845551212', $request);
            return true;
        }))->willReturn($response);

        $messages = $this->messageClient->get($query);
        $body = json_decode($response->getBody(), true);

        $this->assertSame($body['count'], count($messages));
        $this->assertSame($body['items'][0]['message-id'], $messages[0]->getMessageId());
    }

    public function testGetThrowsExceptionWhenNot200ButHasErrorLabel()
    {
        $this->expectException(Exception\Request::class);
        $this->expectExceptionMessage('authentication failed');

        $message = new Message('02000000D912945A');
        $response = $this->getResponse('auth-failure', 401);

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);
            return true;
        }))->willReturn($response);

        $this->messageClient->get($message);
    }

    public function testGetThrowsExceptionWhenNot200AndHasNoCode()
    {
        $this->expectException(Exception\Request::class);
        $this->expectExceptionMessage('error status from API');

        $message = new Message('02000000D912945A');
        $response = $this->getResponse('empty', 500);

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);
            return true;
        }))->willReturn($response);

        $this->messageClient->get($message);
    }

    public function testGetThrowsExceptionWhenInvalidResponseReturned()
    {
        $this->expectException(Exception\Request::class);
        $this->expectExceptionMessage('unexpected response from API');

        $message = new Message('02000000D912945A');
        $response = $this->getResponse('empty');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);
            return true;
        }))->willReturn($response);

        $this->messageClient->get($message);
    }

    public function testGetThrowsInvalidArgumentExceptionWithBadQuery()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('query must be an instance of Query, MessageInterface, string ID, or array of IDs.');

        $message = new \stdClass;
        $message->ids = ['02000000D912945A'];
        $response = $this->getResponse('empty');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);
            return true;
        }))->willReturn($response);

        $this->messageClient->get($message);
    }

    public function testCanSearchByMessage()
    {
        $message = new Message('02000000D912945A');
        $response = $this->getResponse('search-outbound');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('id', '02000000D912945A', $request);
            return true;
        }))->willReturn($response);

        $searchedMessage = $this->messageClient->search($message);

        $response->getBody()->rewind();
        $successData = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals($successData['message-id'], $searchedMessage->getMessageId());
    }

    public function testCanSearchBySingleOutboundId()
    {
        $response = $this->getResponse('search-outbound');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('id', '02000000D912945A', $request);
            return true;
        }))->willReturn($response);

        $message = $this->messageClient->search('02000000D912945A');

        $this->assertInstanceOf('Vonage\Message\Message', $message);
        $this->assertSame($response, @$message->getResponse());
    }

    public function testCanSearchBySingleInboundId()
    {
        $response = $this->getResponse('search-inbound');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('id', '02000000DA7C52E7', $request);
            return true;
        }))->willReturn($response);

        $message = $this->messageClient->search('02000000DA7C52E7');

        $this->assertInstanceOf('Vonage\Message\InboundMessage', $message);
        $this->assertSame($response, @$message->getResponse());
    }

    public function testSearchThrowsExceptionOnEmptySearchSet()
    {
        $this->expectException(Exception\Request::class);
        $this->expectExceptionMessage('no message found for `02000000DA7C52E7`');
        $response = $this->getResponse('search-empty');

        $this->vonageClient->send(Argument::that(function(Request $request) {
            $this->assertRequestQueryContains('id', '02000000DA7C52E7', $request);
            return true;
        }))->willReturn($response);

        $this->messageClient->search('02000000DA7C52E7');
    }

    public function testSearchThrowExceptionOnNon200()
    {
        $this->expectException(Exception\Request::class);
        $this->expectExceptionMessage('authentication failed');

        $message = new Message('02000000D912945A');
        $response = $this->getResponse('auth-failure', 401);

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('id', '02000000D912945A', $request);
            return true;
        }))->willReturn($response);

        $this->messageClient->search($message);
    }

    public function testSearchThrowExceptionOnInvalidType()
    {
        $this->expectException(Exception\Request::class);
        $this->expectExceptionMessage('unexpected response from API');

        $message = new Message('02000000D912945A');
        $response = $this->getResponse('search-invalid-type');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('id', '02000000D912945A', $request);
            return true;
        }))->willReturn($response);

        $this->messageClient->search($message);
    }

    public function testSearchThrowsGenericExceptionOnNon200()
    {
        $this->expectException(Exception\Request::class);
        $this->expectExceptionMessage('error status from API');

        $message = new Message('02000000D912945A');
        $response = $this->getResponse('empty', 500);

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('id', '02000000D912945A', $request);
            return true;
        }))->willReturn($response);

        $this->messageClient->search($message);
    }

    public function testThrowsExceptionWhenSearchResultMismatchesQuery()
    {

        $this->expectException(Exception\Exception::class);
        $this->expectExceptionMessage('searched for message with type `Vonage\Message\Message` but message of type `Vonage\Message\InboundMessage`');

        $message = new Message('02000000D912945A');
        $response = $this->getResponse('search-inbound');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('id', '02000000D912945A', $request);
            return true;
        }))->willReturn($response);

        $this->messageClient->search($message);
    }

    public function testRateLimitRetries()
    {
        $rate    = $this->getResponse('ratelimit');
        $rate2    = $this->getResponse('ratelimit');
        $success = $this->getResponse('success');

        $args = [
            'to' => '14845551345',
            'from' => '1105551334',
            'text' => 'test message'
        ];

        $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
            $this->assertRequestJsonBodyContains('to', $args['to'], $request);
            $this->assertRequestJsonBodyContains('from', $args['from'], $request);
            $this->assertRequestJsonBodyContains('text', $args['text'], $request);
            return true;
        }))->willReturn($rate, $rate2, $success);

        $message = $this->messageClient->send(new Text($args['to'], $args['from'], $args['text']));
        $this->assertEquals($success, @$message->getResponse());
    }

    public function testRateLimitRetriesWithDefault()
    {
        $rate    = $this->getResponse('ratelimit-notime');
        $rate2    = $this->getResponse('ratelimit-notime'); // Have to duplicate to avoid rewind issues
        $success = $this->getResponse('success');

        $args = [
            'to' => '14845551345',
            'from' => '1105551334',
            'text' => 'test message'
        ];

        $this->vonageClient->send(Argument::that(function (Request $request) use ($args)  {
            $this->assertRequestJsonBodyContains('to', $args['to'], $request);
            $this->assertRequestJsonBodyContains('from', $args['from'], $request);
            $this->assertRequestJsonBodyContains('text', $args['text'], $request);
            return true;
        }))->willReturn($rate, $rate2, $success);

        $message = $this->messageClient->send(new Text($args['to'], $args['from'], $args['text']));

        $success->getBody()->rewind();
        $successData = json_decode($success->getBody()->getContents(), true);
        $this->assertEquals($successData['messages'][0]['message-id'], $message->getMessageId());
    }

    /**
     * @dataProvider searchRejectionsProvider
     */
    public function testCanSearchRejections($date, $to, $responseFile, $expectedResponse, $expectedHttpCode, $expectedException)
    {
        $query = new \Vonage\Message\Query($date, $to);

        $apiResponse = $this->getResponse($responseFile, $expectedHttpCode);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($to, $date) {
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
        @$message->setResponse($this->getResponse('search-rejections'));
        $message->setIndex(0);
        $inboundMessage = new InboundMessage('0C0000005BA0B864');
        @$inboundMessage->setResponse($this->getResponse('search-rejections-inbound'));
        $inboundMessage->setIndex(0);

        $r['rejection found'] = [new \DateTime(), '123456', 'search-rejections', [$message], 200, null];
        $r['inbound rejection found'] = [new \DateTime(), '123456', 'search-rejections-inbound', [$inboundMessage], 200, null];

        $r['error-code provided (validation)'] = [new \DateTime(), '123456', 'search-rejections-error-provided-validation', 'Validation error: You forgot to do something', 400, Exception\Request::class];
        $r['error-code provided (server error)'] = [new \DateTime(), '123456', 'search-rejections-error-provided-server-error', 'Gremlins! There are gremlins in the system!', 500, Exception\Request::class];
        $r['error-code not provided'] = [new \DateTime(), '123456', 'empty', 'error status from API', 500, Exception\Request::class];
        $r['missing items key in response on 200'] = [new \DateTime(), '123456', 'empty', 'unexpected response from API', 200, Exception\Exception::class];
        $r['invalid message type in response'] = [new \DateTime(), '123456', 'search-rejections-invalid-type', 'unexpected response from API', 200, Exception\Request::class];

        return $r;
    }

    public function testShortcodeWithObject()
    {
        $message = new TwoFactor('14155550100', [ 'link' => 'https://example.com' ], ['status-report-req' => 1]);

        $this->vonageClient->send(Argument::that(function(Request $request) {
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

        $this->vonageClient->send(Argument::that(function(Request $request) use ($args){
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

        $this->vonageClient->send(Argument::that(function(Request $request) use ($args){
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

    public function testCreateMessageThrowsExceptionOnBadData()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('message must implement `Vonage\Message\MessageInterface` or be an array`');

        @$this->messageClient->send("Bob");
    }

    public function testCreateMessageThrowsExceptionOnMissingData()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('missing expected key `from`');

        @$this->messageClient->send(['to' => '15555555555']);
    }

    public function testMagicMethodIsCalledProperly()
    {
        $args = [
            'to' => '14845551212',
            'from' => '16105551212',
            'text' => 'Go To Gino\'s'
        ];

        $this->vonageClient->send(Argument::that(function (Request $request) use ($args){
            $this->assertRequestJsonBodyContains('to', $args['to'], $request);
            $this->assertRequestJsonBodyContains('from', $args['from'], $request);
            $this->assertRequestJsonBodyContains('text', $args['text'], $request);
            return true;
        }))->willReturn($this->getResponse());

        $message = $this->messageClient->sendText($args['to'], $args['from'], $args['text']);
        $this->assertInstanceOf('Vonage\Message\Text', $message);
    }

    public function testCreateMessageThrowsExceptionOnNonSendMethod()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('failsendText` is not a valid method on `Vonage\Message\Client`');

        $this->messageClient->failsendText('14845551212', '16105551212', 'Test');
    }

    public function testCreateMessageThrowsExceptionOnNonSendMethodTakeTwo()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('failText` is not a valid method on `Vonage\Message\Client`');

        $this->messageClient->failText('14845551212', '16105551212', 'Test');
    }

    public function testCreateMessageThrowsExceptionOnInvalidMessageType()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('sendGarbage` is not a valid method on `Vonage\Message\Client`');

        $this->messageClient->sendGarbage('14845551212', '16105551212', 'Test');
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
