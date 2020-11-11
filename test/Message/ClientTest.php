<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Message;

use DateTime;
use Exception;
use InvalidArgumentException;
use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use RuntimeException;
use stdClass;
use Vonage\Client;
use Vonage\Client\Exception as ClientException;
use Vonage\Client\Exception\Server as ServerException;
use Vonage\Message\Client as MessageClient;
use Vonage\Message\InboundMessage;
use Vonage\Message\Message;
use Vonage\Message\Query;
use Vonage\Message\Shortcode\TwoFactor;
use Vonage\Message\Text;
use VonageTest\MessageAssertionTrait;
use VonageTest\Psr7AssertionTrait;

use function fopen;
use function json_decode;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;
    use MessageAssertionTrait;

    protected $vonageClient;

    /**
     * @var MessageClient
     */
    protected $messageClient;

    /**
     * Create the Message API Client, and mock the Vonage Client
     */
    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getRestUrl()->willReturn('https://rest.nexmo.com');
        $this->messageClient = new MessageClient();

        /** @noinspection PhpParamsInspection */
        $this->messageClient->setClient($this->vonageClient->reveal());
    }

    /**
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ServerException
     * @throws ClientExceptionInterface
     */
    public function testCanUseMessage(): void
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

        $message = @$this->messageClient->send(new Text($args['to'], $args['from'], $args['text']));

        $this->assertInstanceOf(Text::class, $message);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ServerException
     */
    public function testThrowsRequestExceptionWhenInvalidAPIResponse(): void
    {
        $this->expectException(ClientException\Request::class);
        $this->expectExceptionMessage('unexpected response from API');

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
        }))->willReturn($this->getResponse('empty'));

        $this->messageClient->send(new Text($args['to'], $args['from'], $args['text']));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ServerException
     */
    public function testCanUseArguments(): void
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
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ServerException
     */
    public function testSentMessageHasResponse(): void
    {
        $response = $this->getResponse();
        @$this->vonageClient->send(Argument::type(RequestInterface::class))->willReturn($response);
        $message = $this->messageClient->send(new Text('14845551212', '16105551212', 'Not Pats?'));

        $this->assertSame($response, @$message->getResponse());

        $this->vonageClient->send(@$message->getRequest())->shouldHaveBeenCalled();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ServerException
     */
    public function testThrowRequestException(): void
    {
        $response = $this->getResponse('fail');
        $this->vonageClient->send(Argument::type(RequestInterface::class))->willReturn($response);
        $message = new Text('14845551212', '16105551212', 'Not Pats?');

        try {
            $this->messageClient->send($message);

            self::fail('did not throw exception');
        } catch (ClientException\Request $e) {
            $this->assertSame($message, $e->getEntity());
            $this->assertEquals('2', $e->getCode());
            $this->assertEquals('Missing from param', $e->getMessage());
        }
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function testThrowServerException(): void
    {
        $response = $this->getResponse('fail-server');
        $this->vonageClient->send(Argument::type(RequestInterface::class))->willReturn($response);
        $message = new Text('14845551212', '16105551212', 'Not Pats?');

        try {
            $this->messageClient->send($message);

            self::fail('did not throw exception');
        } catch (ServerException $e) {
            $this->assertEquals('5', $e->getCode());
            $this->assertEquals('Server Error', $e->getMessage());
        }
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     */
    public function testThrowConcurrentRequestsException(): void
    {
        try {
            $message = new Message('02000000D912945A');
            $response = $this->getResponse('empty', 429);

            $this->vonageClient->send(Argument::that(function (Request $request) {
                $this->assertRequestQueryContains('id', '02000000D912945A', $request);
                return true;
            }))->willReturn($response);

            $this->messageClient->search($message);

            self::fail('did not throw exception');
        } catch (ClientException\Request $e) {
            $this->assertEquals('429', $e->getCode());
            $this->assertEquals('too many concurrent requests', $e->getMessage());
        }
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function testCanGetMessageWithMessageObject(): void
    {
        $message = new Message('02000000D912945A');
        $response = $this->getResponse('get-outbound');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);

            return true;
        }))->willReturn($response);

        $messages = $this->messageClient->get($message);

        // The response was already read, so have to rewind
        $response->getBody()->rewind();
        $body = json_decode($response->getBody()->getContents(), true);

        $this->assertCount($body['count'], $messages);
        $this->assertSame($body['items'][0]['message-id'], $messages[0]->getMessageId());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function testCanGetInboundMessage(): void
    {
        $message = new Message('0B00000053FFB40F');
        $response = $this->getResponse('get-inbound');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('ids', ['0B00000053FFB40F'], $request);

            return true;
        }))->willReturn($response);

        $messages = $this->messageClient->get($message);

        // The response was already read, so need to rewind
        $response->getBody()->rewind();
        $body = json_decode($response->getBody()->getContents(), true);

        $this->assertCount($body['count'], $messages);
        $this->assertSame($body['items'][0]['message-id'], $messages[0]->getMessageId());
        $this->assertInstanceOf(InboundMessage::class, $messages[0]);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function testGetThrowsExceptionOnBadMessageType(): void
    {
        $this->expectException(ClientException\Request::class);
        $this->expectExceptionMessage('unexpected response from API');

        $message = new Message('0B00000053FFB40F');
        $response = $this->getResponse('get-invalid-type');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('ids', ['0B00000053FFB40F'], $request);
            return true;
        }))->willReturn($response);

        $this->messageClient->get($message);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function testGetReturnsEmptyArrayWithNoResults(): void
    {
        $message = new Message('02000000D912945A');
        $response = $this->getResponse('get-no-results');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);

            return true;
        }))->willReturn($response);

        $messages = $this->messageClient->get($message);

        $this->assertCount(0, $messages);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function testCanGetMessageWithStringID(): void
    {
        $messageID = '02000000D912945A';
        $response = $this->getResponse('get-outbound');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);

            return true;
        }))->willReturn($response);

        $messages = $this->messageClient->get($messageID);

        // The response was already read, so need to rewind
        $response->getBody()->rewind();
        $body = json_decode($response->getBody()->getContents(), true);

        $this->assertCount($body['count'], $messages);
        $this->assertSame($body['items'][0]['message-id'], $messages[0]->getMessageId());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function testCanGetMessageWithArrayOfIDs(): void
    {
        $messageIDs = ['02000000D912945A'];
        $response = $this->getResponse('get-outbound');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);

            return true;
        }))->willReturn($response);

        $messages = $this->messageClient->get($messageIDs);

        // The response was already read, so need to rewind
        $response->getBody()->rewind();
        $body = json_decode($response->getBody()->getContents(), true);

        $this->assertCount($body['count'], $messages);
        $this->assertSame($body['items'][0]['message-id'], $messages[0]->getMessageId());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function testCanGetMessageWithQuery(): void
    {
        $query = new Query(new DateTime('2016-05-19'), '14845551212');
        $response = $this->getResponse('get-outbound');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('date', '2016-05-19', $request);
            $this->assertRequestQueryContains('to', '14845551212', $request);

            return true;
        }))->willReturn($response);

        $messages = $this->messageClient->get($query);

        // The response was already read, so need to rewind
        $response->getBody()->rewind();
        $body = json_decode($response->getBody()->getContents(), true);

        $this->assertCount($body['count'], $messages);
        $this->assertSame($body['items'][0]['message-id'], $messages[0]->getMessageId());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function testGetThrowsExceptionWhenNot200ButHasErrorLabel(): void
    {
        $this->expectException(ClientException\Request::class);
        $this->expectExceptionMessage('authentication failed');

        $message = new Message('02000000D912945A');
        $response = $this->getResponse('auth-failure', 401);

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);

            return true;
        }))->willReturn($response);

        $this->messageClient->get($message);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function testGetThrowsExceptionWhenNot200AndHasNoCode(): void
    {
        $this->expectException(ClientException\Request::class);
        $this->expectExceptionMessage('error status from API');

        $message = new Message('02000000D912945A');
        $response = $this->getResponse('empty', 500);

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);

            return true;
        }))->willReturn($response);

        $this->messageClient->get($message);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function testGetThrowsExceptionWhenInvalidResponseReturned(): void
    {
        $this->expectException(ClientException\Request::class);
        $this->expectExceptionMessage('unexpected response from API');

        $message = new Message('02000000D912945A');
        $response = $this->getResponse('empty');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);

            return true;
        }))->willReturn($response);

        $this->messageClient->get($message);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function testGetThrowsInvalidArgumentExceptionWithBadQuery(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'query must be an instance of Query, MessageInterface, string ID, or array of IDs.'
        );

        $message = new stdClass();
        $message->ids = ['02000000D912945A'];
        $response = $this->getResponse('empty');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);

            return true;
        }))->willReturn($response);

        $this->messageClient->get($message);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws Exception
     */
    public function testCanSearchByMessage(): void
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

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function testCanSearchBySingleOutboundId(): void
    {
        $response = $this->getResponse('search-outbound');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('id', '02000000D912945A', $request);

            return true;
        }))->willReturn($response);

        $message = $this->messageClient->search('02000000D912945A');

        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame($response, @$message->getResponse());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function testCanSearchBySingleInboundId(): void
    {
        $response = $this->getResponse('search-inbound');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('id', '02000000DA7C52E7', $request);

            return true;
        }))->willReturn($response);

        $message = $this->messageClient->search('02000000DA7C52E7');

        $this->assertInstanceOf(InboundMessage::class, $message);
        $this->assertSame($response, @$message->getResponse());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function testSearchThrowsExceptionOnEmptySearchSet(): void
    {
        $this->expectException(ClientException\Request::class);
        $this->expectExceptionMessage('no message found for `02000000DA7C52E7`');
        $response = $this->getResponse('search-empty');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('id', '02000000DA7C52E7', $request);

            return true;
        }))->willReturn($response);

        $this->messageClient->search('02000000DA7C52E7');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function testSearchThrowExceptionOnNon200(): void
    {
        $this->expectException(ClientException\Request::class);
        $this->expectExceptionMessage('authentication failed');

        $message = new Message('02000000D912945A');
        $response = $this->getResponse('auth-failure', 401);

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('id', '02000000D912945A', $request);

            return true;
        }))->willReturn($response);

        $this->messageClient->search($message);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function testSearchThrowExceptionOnInvalidType(): void
    {
        $this->expectException(ClientException\Request::class);
        $this->expectExceptionMessage('unexpected response from API');

        $message = new Message('02000000D912945A');
        $response = $this->getResponse('search-invalid-type');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('id', '02000000D912945A', $request);

            return true;
        }))->willReturn($response);

        $this->messageClient->search($message);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function testSearchThrowsGenericExceptionOnNon200(): void
    {
        $this->expectException(ClientException\Request::class);
        $this->expectExceptionMessage('error status from API');

        $message = new Message('02000000D912945A');
        $response = $this->getResponse('empty', 500);

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('id', '02000000D912945A', $request);

            return true;
        }))->willReturn($response);

        $this->messageClient->search($message);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function testThrowsExceptionWhenSearchResultMismatchesQuery(): void
    {
        $this->expectException(ClientException\Exception::class);
        $this->expectExceptionMessage('searched for message with type `Vonage\Message\Message` ' .
            'but message of type `Vonage\Message\InboundMessage`');

        $message = new Message('02000000D912945A');
        $response = $this->getResponse('search-inbound');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('id', '02000000D912945A', $request);

            return true;
        }))->willReturn($response);

        $this->messageClient->search($message);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ServerException
     */
    public function testRateLimitRetries(): void
    {
        $rate = $this->getResponse('ratelimit');
        $rate2 = $this->getResponse('ratelimit');
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

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ServerException
     * @throws Exception
     */
    public function testRateLimitRetriesWithDefault(): void
    {
        $rate = $this->getResponse('ratelimit-notime');
        $rate2 = $this->getResponse('ratelimit-notime'); // Have to duplicate to avoid rewind issues
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

        $success->getBody()->rewind();
        $successData = json_decode($success->getBody()->getContents(), true);

        $this->assertEquals($successData['messages'][0]['message-id'], $message->getMessageId());
    }

    /**
     * @dataProvider searchRejectionsProvider
     *
     * @param $date
     * @param $to
     * @param $responseFile
     * @param $expectedResponse
     * @param $expectedHttpCode
     * @param $expectedException
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function testCanSearchRejections(
        $date,
        $to,
        $responseFile,
        $expectedResponse,
        $expectedHttpCode,
        $expectedException
    ): void {
        $query = new Query($date, $to);

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

    public function searchRejectionsProvider(): array
    {
        $r = [];

        $r['no rejections found'] = [new DateTime(), '123456', 'search-rejections-empty', [], 200, null];

        // Build up our expected message object
        $message = new Message('0C0000005BA0B864');
        @$message->setResponse($this->getResponse('search-rejections'));
        $message->setIndex(0);
        $inboundMessage = new InboundMessage('0C0000005BA0B864');
        @$inboundMessage->setResponse($this->getResponse('search-rejections-inbound'));
        $inboundMessage->setIndex(0);

        $r['rejection found'] = [new DateTime(), '123456', 'search-rejections', [$message], 200, null];
        $r['inbound rejection found'] = [
            new DateTime(),
            '123456',
            'search-rejections-inbound',
            [$inboundMessage],
            200,
            null
        ];

        $r['error-code provided (validation)'] = [
            new DateTime(),
            '123456',
            'search-rejections-error-provided-validation',
            'Validation error: You forgot to do something',
            400,
            ClientException\Request::class
        ];

        $r['error-code provided (server error)'] = [
            new DateTime(),
            '123456',
            'search-rejections-error-provided-server-error',
            'Gremlins! There are gremlins in the system!',
            500,
            ClientException\Request::class
        ];

        $r['error-code not provided'] = [
            new DateTime(),
            '123456',
            'empty',
            'error status from API',
            500,
            ClientException\Request::class
        ];

        $r['missing items key in response on 200'] = [
            new DateTime(),
            '123456',
            'empty',
            'unexpected response from API',
            200,
            ClientException\Exception::class
        ];

        $r['invalid message type in response'] = [
            new DateTime(),
            '123456',
            'search-rejections-invalid-type',
            'unexpected response from API',
            200,
            ClientException\Request::class
        ];

        return $r;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function testShortcodeWithObject(): void
    {
        $message = new TwoFactor('14155550100', ['link' => 'https://example.com'], ['status-report-req' => 1]);

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestJsonBodyContains('to', '14155550100', $request);
            $this->assertRequestJsonBodyContains('link', 'https://example.com', $request);
            $this->assertRequestJsonBodyContains('status-report-req', 1, $request);

            return true;
        }))->willReturn($this->getResponse('success-2fa'));

        $response = $this->messageClient->sendShortcode($message);

        $this->assertEquals([
            'message-count' => '1',
            'messages' => [
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
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function testShortcodeError(): void
    {
        $args = [
            'to' => '14155550100',
            'custom' => ['link' => 'https://example.com'],
            'options' => ['status-report-req' => 1],
            'type' => '2fa'
        ];

        $this->vonageClient->send(Argument::that(function (Request $request) {
            return true;
        }))->willReturn($this->getResponse('error-2fa'));

        $this->expectException(ClientException\Request::class);
        $this->expectExceptionMessage('Invalid Account for Campaign');

        $this->messageClient->sendShortcode($args);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function testShortcodeWithArray(): void
    {
        $args = [
            'to' => '14155550100',
            'custom' => ['link' => 'https://example.com'],
            'options' => ['status-report-req' => 1],
            'type' => '2fa'
        ];

        $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
            $this->assertRequestJsonBodyContains('to', $args['to'], $request);
            $this->assertRequestJsonBodyContains('link', $args['custom']['link'], $request);
            $this->assertRequestJsonBodyContains('status-report-req', $args['options']['status-report-req'], $request);

            return true;
        }))->willReturn($this->getResponse('success-2fa'));

        $response = $this->messageClient->sendShortcode($args);

        $this->assertEquals([
            'message-count' => '1',
            'messages' => [
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
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ServerException
     */
    public function testCreateMessageThrowsExceptionOnBadData(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('message must implement `Vonage\Message\MessageInterface` or be an array`');

        /** @noinspection PhpParamsInspection */
        @$this->messageClient->send('Bob');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ServerException
     */
    public function testCreateMessageThrowsExceptionOnMissingData(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('missing expected key `from`');

        @$this->messageClient->send(['to' => '15555555555']);
    }

    public function testMagicMethodIsCalledProperly(): void
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

        $message = $this->messageClient->sendText($args['to'], $args['from'], $args['text']);
        $this->assertInstanceOf(Text::class, $message);
    }

    public function testCreateMessageThrowsExceptionOnNonSendMethod(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('failsendText` is not a valid method on `Vonage\Message\Client`');

        /** @noinspection PhpUndefinedMethodInspection */
        $this->messageClient->failsendText('14845551212', '16105551212', 'Test');
    }

    public function testCreateMessageThrowsExceptionOnNonSendMethodTakeTwo(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('failText` is not a valid method on `Vonage\Message\Client`');

        /** @noinspection PhpUndefinedMethodInspection */
        $this->messageClient->failText('14845551212', '16105551212', 'Test');
    }

    public function testCreateMessageThrowsExceptionOnInvalidMessageType(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('sendGarbage` is not a valid method on `Vonage\Message\Client`');

        /** @noinspection PhpUndefinedMethodInspection */
        $this->messageClient->sendGarbage('14845551212', '16105551212', 'Test');
    }

    /**
     * Get the API response we'd expect for a call to the API. Message API currently returns 200 all the time, so only
     * change between success / fail is body of the message.
     */
    protected function getResponse(string $type = 'success', int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
    }
}
