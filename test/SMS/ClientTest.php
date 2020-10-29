<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\SMS;

use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Exception\Server as ServerException;
use Vonage\SMS\Client as SMSClient;
use Vonage\SMS\ExceptionErrorHandler;
use Vonage\SMS\Message\SMS;
use VonageTest\Psr7AssertionTrait;

use function fopen;
use function json_decode;
use function str_repeat;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    /**
     * @var APIResource
     */
    protected $api;

    /**
     * @var mixed
     */
    protected $vonageClient;

    /**
     * @var SMSClient
     */
    protected $smsClient;

    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getRestUrl()->willReturn('https://rest.nexmo.com');
        /** @noinspection PhpParamsInspection */
        $this->api = (new APIResource())
            ->setCollectionName('messages')
            ->setIsHAL(false)
            ->setErrorsOn200(true)
            ->setClient($this->vonageClient->reveal())
            ->setExceptionErrorHandler(new ExceptionErrorHandler())
            ->setBaseUrl('https://rest.nexmo.com');
        $this->smsClient = new SMSClient($this->api);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanSendSMS(): void
    {
        $args = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => "Go To Gino's",
            'account-ref' => 'customer1234',
            'client-ref' => 'my-personal-reference'
        ];

        $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
            self::assertRequestJsonBodyContains('to', $args['to'], $request);
            self::assertRequestJsonBodyContains('from', $args['from'], $request);
            self::assertRequestJsonBodyContains('text', $args['text'], $request);
            self::assertRequestJsonBodyContains('account-ref', $args['account-ref'], $request);
            self::assertRequestJsonBodyContains('client-ref', $args['client-ref'], $request);

            return true;
        }))->willReturn($this->getResponse('send-success'));

        $message = (new SMS($args['to'], $args['from'], $args['text']))
            ->setClientRef($args['client-ref'])
            ->setAccountRef($args['account-ref']);
        $response = $this->smsClient->send($message);
        $sentData = $response->current();

        self::assertCount(1, $response);
        self::assertSame($args['to'], $sentData->getTo());
        self::assertSame('0A0000000123ABCD1', $sentData->getMessageId());
        self::assertSame("0.03330000", $sentData->getMessagePrice());
        self::assertSame("12345", $sentData->getNetwork());
        self::assertSame("3.14159265", $sentData->getRemainingBalance());
        self::assertSame("customer1234", $sentData->getAccountRef());
        self::assertSame("my-personal-reference", $sentData->getClientRef());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testHandlesEmptyResponse(): void
    {
        $this->expectException(Client\Exception\Request::class);
        $this->expectExceptionMessage('unexpected response from API');

        $this->vonageClient
            ->send(Argument::type(RequestInterface::class))
            ->willReturn($this->getResponse('empty'));

        $this->smsClient->send(new SMS('14845551212', '16105551212', "Go To Gino's"));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanParseErrorsAndThrowException(): void
    {
        $this->expectException(Client\Exception\Request::class);
        $this->expectExceptionMessage('Missing from param');

        $this->vonageClient
            ->send(Argument::type(RequestInterface::class))
            ->willReturn($this->getResponse('fail'));

        $this->smsClient->send(new SMS('14845551212', '16105551212', "Go To Gino's"));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanParseServerErrorsAndThrowException(): void
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('Server Error');

        $this->vonageClient
            ->send(Argument::type(RequestInterface::class))
            ->willReturn($this->getResponse('fail-server'));

        $this->smsClient->send(new SMS('14845551212', '16105551212', "Go To Gino's"));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanHandleRateLimitRequests(): void
    {
        $rate = $this->getResponse('ratelimit');
        $rate2 = $this->getResponse('ratelimit');
        $success = $this->getResponse('send-success');
        $args = [
            'to' => '447700900000',
            'from' => '1105551334',
            'text' => 'test message'
        ];

        $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
            self::assertRequestJsonBodyContains('to', $args['to'], $request);
            self::assertRequestJsonBodyContains('from', $args['from'], $request);
            self::assertRequestJsonBodyContains('text', $args['text'], $request);

            return true;
        }))->willReturn($rate, $rate2, $success);

        $response = $this->smsClient->send(new SMS($args['to'], $args['from'], $args['text']));
        $sentData = $response->current();

        self::assertCount(1, $response);
        self::assertSame($args['to'], $sentData->getTo());
        self::assertSame('0A0000000123ABCD1', $sentData->getMessageId());
        self::assertSame("0.03330000", $sentData->getMessagePrice());
        self::assertSame("12345", $sentData->getNetwork());
        self::assertSame("3.14159265", $sentData->getRemainingBalance());
        self::assertSame(0, $sentData->getStatus());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanHandleRateLimitRequestsWithNoDeclaredTimeout(): void
    {
        $rate = $this->getResponse('ratelimit-notime');
        $rate2 = $this->getResponse('ratelimit-notime');
        $success = $this->getResponse('send-success');

        $args = [
            'to' => '447700900000',
            'from' => '1105551334',
            'text' => 'test message'
        ];

        $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
            self::assertRequestJsonBodyContains('to', $args['to'], $request);
            self::assertRequestJsonBodyContains('from', $args['from'], $request);
            self::assertRequestJsonBodyContains('text', $args['text'], $request);

            return true;
        }))->willReturn($rate, $rate2, $success);

        $response = $this->smsClient->send(new SMS($args['to'], $args['from'], $args['text']));
        $sentData = $response->current();

        self::assertCount(1, $response);
        self::assertSame($args['to'], $sentData->getTo());
        self::assertSame('0A0000000123ABCD1', $sentData->getMessageId());
        self::assertSame("0.03330000", $sentData->getMessagePrice());
        self::assertSame("12345", $sentData->getNetwork());
        self::assertSame("3.14159265", $sentData->getRemainingBalance());
        self::assertSame(0, $sentData->getStatus());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanUnderstandMultiMessageResponses(): void
    {
        $args = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => str_repeat('This is an incredibly large SMS message', 5)
        ];

        $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
            self::assertRequestJsonBodyContains('to', $args['to'], $request);
            self::assertRequestJsonBodyContains('from', $args['from'], $request);
            self::assertRequestJsonBodyContains('text', $args['text'], $request);

            return true;
        }))->willReturn($this->getResponse('multi'));

        $response = $this->smsClient->send((new SMS($args['to'], $args['from'], $args['text'])));
        $rawData = json_decode($this->getResponse('multi')->getBody()->getContents(), true);

        self::assertCount((int)$rawData['message-count'], $response);

        foreach ($response as $key => $sentData) {
            self::assertSame($rawData['messages'][$key]['to'], $sentData->getTo());
            self::assertSame($rawData['messages'][$key]['message-id'], $sentData->getMessageId());
            self::assertSame($rawData['messages'][$key]['message-price'], $sentData->getMessagePrice());
            self::assertSame($rawData['messages'][$key]['network'], $sentData->getNetwork());
            self::assertSame($rawData['messages'][$key]['remaining-balance'], $sentData->getRemainingBalance());
            self::assertSame((int)$rawData['messages'][$key]['status'], $sentData->getStatus());
        }
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanSend2FAMessage(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            self::assertRequestJsonBodyContains('to', '447700900000', $request);
            self::assertRequestJsonBodyContains('pin', 1245, $request);

            return true;
        }))->willReturn($this->getResponse('send-success'));

        $sentData = $this->smsClient->sendTwoFactor('447700900000', 1245);

        self::assertSame('447700900000', $sentData->getTo());
        self::assertSame('0A0000000123ABCD1', $sentData->getMessageId());
        self::assertSame("0.03330000", $sentData->getMessagePrice());
        self::assertSame("12345", $sentData->getNetwork());
        self::assertSame("3.14159265", $sentData->getRemainingBalance());
        self::assertSame(0, $sentData->getStatus());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanHandleMissingShortcodeOn2FA(): void
    {
        $this->expectException(Client\Exception\Request::class);
        $this->expectExceptionMessage('Invalid Account for Campaign');
        $this->expectExceptionCode(101);

        $this->vonageClient
            ->send(Argument::type(RequestInterface::class))
            ->willReturn($this->getResponse('fail-shortcode'));
        $this->smsClient->sendTwoFactor('447700900000', 1245);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanSendAlert(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            self::assertRequestJsonBodyContains('to', '447700900000', $request);
            self::assertRequestJsonBodyContains('key', 'value', $request);

            return true;
        }))->willReturn($this->getResponse('send-success'));

        $response = $this->smsClient->sendAlert('447700900000', ['key' => 'value']);
        $sentData = $response->current();

        self::assertCount(1, $response);
        self::assertSame('447700900000', $sentData->getTo());
        self::assertSame('0A0000000123ABCD1', $sentData->getMessageId());
        self::assertSame("0.03330000", $sentData->getMessagePrice());
        self::assertSame("12345", $sentData->getNetwork());
        self::assertSame("3.14159265", $sentData->getRemainingBalance());
        self::assertSame(0, $sentData->getStatus());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanHandleMissingAlertSetup(): void
    {
        $this->expectException(Client\Exception\Request::class);
        $this->expectExceptionMessage('Invalid Account for Campaign');
        $this->expectExceptionCode(101);

        $this->vonageClient
            ->send(Argument::type(RequestInterface::class))
            ->willReturn($this->getResponse('fail-shortcode'));
        $this->smsClient->sendAlert('447700900000', ['key' => 'value']);
    }

    /**
     * Get the API response we'd expect for a call to the API. Message API currently returns 200 all the time, so only
     * change between success / fail is body of the message.
     *
     * @param string $type
     * @param int $status
     *
     * @return Response
     */
    protected function getResponse(string $type = 'success', int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
    }
}
