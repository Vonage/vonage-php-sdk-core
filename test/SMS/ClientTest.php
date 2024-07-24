<?php

declare(strict_types=1);

namespace VonageTest\SMS;

use Laminas\Diactoros\Request;
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Exception\Server as ServerException;
use Vonage\SMS\Client as SMSClient;
use Vonage\SMS\ExceptionErrorHandler;
use Vonage\SMS\Message\SMS;
use VonageTest\Traits\HTTPTestTrait;
use VonageTest\Traits\Psr7AssertionTrait;
use VonageTest\VonageTestCase;
use function json_decode;
use function str_repeat;

class ClientTest extends VonageTestCase
{
    use Psr7AssertionTrait;
    use HTTPTestTrait;

    /**
     * @var APIResource
     */
    protected $api;

    protected $vonageClient;

    /**
     * @var SMSClient
     */
    protected $smsClient;

    public function setUp(): void
    {
        $this->responsesDirectory = __DIR__ . '/responses';

        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getRestUrl()->willReturn('https://rest.nexmo.com');
        $this->vonageClient->getCredentials()->willReturn(
            new Client\Credentials\Container(
                new Client\Credentials\Basic('abc', 'def'),
                new Client\Credentials\SignatureSecret('erwer', 'dfsiodgsjk')
            )
        );

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
            $this->assertRequestJsonBodyContains('to', $args['to'], $request);
            $this->assertRequestJsonBodyContains('from', $args['from'], $request);
            $this->assertRequestJsonBodyContains('text', $args['text'], $request);
            $this->assertRequestJsonBodyContains('account-ref', $args['account-ref'], $request);
            $this->assertRequestJsonBodyContains('client-ref', $args['client-ref'], $request);

            return true;
        }))->willReturn($this->getResponse('send-success'));

        $message = (new SMS($args['to'], $args['from'], $args['text']))
            ->setClientRef($args['client-ref'])
            ->setAccountRef($args['account-ref']);
        $response = $this->smsClient->send($message);
        $sentData = $response->current();

        $this->assertCount(1, $response);
        $this->assertSame($args['to'], $sentData->getTo());
        $this->assertSame('0A0000000123ABCD1', $sentData->getMessageId());
        $this->assertSame("0.03330000", $sentData->getMessagePrice());
        $this->assertSame("12345", $sentData->getNetwork());
        $this->assertSame("3.14159265", $sentData->getRemainingBalance());
        $this->assertSame("customer1234", $sentData->getAccountRef());
        $this->assertSame("my-personal-reference", $sentData->getClientRef());
    }

    public function testCanGetSmsRawResponse(): void
    {
        $args = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => "Go To Gino's",
            'account-ref' => 'customer1234',
            'client-ref' => 'my-personal-reference'
        ];

        $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
            $this->assertRequestJsonBodyContains('to', $args['to'], $request);
            $this->assertRequestJsonBodyContains('from', $args['from'], $request);
            $this->assertRequestJsonBodyContains('text', $args['text'], $request);
            $this->assertRequestJsonBodyContains('account-ref', $args['account-ref'], $request);
            $this->assertRequestJsonBodyContains('client-ref', $args['client-ref'], $request);

            return true;
        }))->willReturn($this->getResponse('send-success'));

        $message = (new SMS($args['to'], $args['from'], $args['text']))
            ->setClientRef($args['client-ref'])
            ->setAccountRef($args['account-ref']);
        $response = $this->smsClient->send($message);
        $rawResponse = $response->getAllMessagesRaw();

        $this->assertCount(2, $rawResponse);
        $this->assertSame('1', $rawResponse['message-count']);
        $this->assertCount(1, $rawResponse['messages']);
        $this->assertSame($args['to'], $rawResponse['messages'][0]['to']);
        $this->assertSame('0A0000000123ABCD1', $rawResponse['messages'][0]['message-id']);
        $this->assertSame("0.03330000", $rawResponse['messages'][0]['message-price']);
        $this->assertSame("12345", $rawResponse['messages'][0]['network']);
        $this->assertSame("3.14159265", $rawResponse['messages'][0]['remaining-balance']);
        $this->assertSame("customer1234", $rawResponse['messages'][0]['account-ref']);
        $this->assertSame("my-personal-reference", $rawResponse['messages'][0]['client-ref']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testHandlesEmptyResponse(): void
    {
        $this->expectException(Client\Exception\Request::class);
        $this->expectExceptionMessage('Unexpected response from the API');

        $this->vonageClient
            ->send(Argument::type(RequestInterface::class))
            ->willReturn($this->getResponse('empty'));

        $this->smsClient->send(new SMS('14845551212', '16105551212', "Go To Gino's"));
    }

    public function testShouldSignatureAuthAsPreferred(): void
    {
        $args = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => "Go To Gino's",
            'account-ref' => 'customer1234',
            'client-ref' => 'my-personal-reference'
        ];

        $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
            $this->assertRequestJsonBodyContains('to', $args['to'], $request);
            $this->assertRequestJsonBodyContains('from', $args['from'], $request);
            $this->assertRequestJsonBodyContains('text', $args['text'], $request);
            $this->assertRequestJsonBodyContains('account-ref', $args['account-ref'], $request);
            $this->assertRequestJsonBodyContains('client-ref', $args['client-ref'], $request);

            return true;
        }))->willReturn($this->getResponse('send-success'));

        $message = (new SMS($args['to'], $args['from'], $args['text']))
            ->setClientRef($args['client-ref'])
            ->setAccountRef($args['account-ref']);
        $response = $this->smsClient->send($message);
        $sentData = $response->current();

        $this->assertCount(1, $response);
        $this->assertSame($args['to'], $sentData->getTo());
        $this->assertSame('0A0000000123ABCD1', $sentData->getMessageId());
        $this->assertSame("0.03330000", $sentData->getMessagePrice());
        $this->assertSame("12345", $sentData->getNetwork());
        $this->assertSame("3.14159265", $sentData->getRemainingBalance());
        $this->assertSame("customer1234", $sentData->getAccountRef());
        $this->assertSame("my-personal-reference", $sentData->getClientRef());
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
        $start = microtime(true);
        $rate = $this->getResponse('ratelimit');
        $rate2 = $this->getResponse('ratelimit');
        $success = $this->getResponse('send-success');
        $args = [
            'to' => '447700900000',
            'from' => '1105551334',
            'text' => 'test message'
        ];

        $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
            $this->assertRequestJsonBodyContains('to', $args['to'], $request);
            $this->assertRequestJsonBodyContains('from', $args['from'], $request);
            $this->assertRequestJsonBodyContains('text', $args['text'], $request);

            return true;
        }))->willReturn($rate, $rate2, $success);

        $response = $this->smsClient->send(new SMS($args['to'], $args['from'], $args['text']));
        $sentData = $response->current();
        $end = microtime(true);

        $this->assertCount(1, $response);
        $this->assertSame($args['to'], $sentData->getTo());
        $this->assertSame('0A0000000123ABCD1', $sentData->getMessageId());
        $this->assertSame("0.03330000", $sentData->getMessagePrice());
        $this->assertSame("12345", $sentData->getNetwork());
        $this->assertSame("3.14159265", $sentData->getRemainingBalance());
        $this->assertSame(0, $sentData->getStatus());
        $this->assertGreaterThanOrEqual(2, $end - $start);
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
            $this->assertRequestJsonBodyContains('to', $args['to'], $request);
            $this->assertRequestJsonBodyContains('from', $args['from'], $request);
            $this->assertRequestJsonBodyContains('text', $args['text'], $request);

            return true;
        }))->willReturn($rate, $rate2, $success);

        $response = $this->smsClient->send(new SMS($args['to'], $args['from'], $args['text']));
        $sentData = $response->current();

        $this->assertCount(1, $response);
        $this->assertSame($args['to'], $sentData->getTo());
        $this->assertSame('0A0000000123ABCD1', $sentData->getMessageId());
        $this->assertSame("0.03330000", $sentData->getMessagePrice());
        $this->assertSame("12345", $sentData->getNetwork());
        $this->assertSame("3.14159265", $sentData->getRemainingBalance());
        $this->assertSame(0, $sentData->getStatus());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanHandleAPIRateLimitRequests(): void
    {
        $start = microtime(true);
        $rate = $this->getResponse('mt-limit');
        $rate2 = $this->getResponse('mt-limit');
        $success = $this->getResponse('send-success');
        $args = [
            'to' => '447700900000',
            'from' => '1105551334',
            'text' => 'test message'
        ];

        $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
            $this->assertRequestJsonBodyContains('to', $args['to'], $request);
            $this->assertRequestJsonBodyContains('from', $args['from'], $request);
            $this->assertRequestJsonBodyContains('text', $args['text'], $request);

            return true;
        }))->willReturn($rate, $rate2, $success);

        $response = $this->smsClient->send(new SMS($args['to'], $args['from'], $args['text']));
        $sentData = $response->current();
        $end = microtime(true);

        $this->assertCount(1, $response);
        $this->assertSame($args['to'], $sentData->getTo());
        $this->assertSame('0A0000000123ABCD1', $sentData->getMessageId());
        $this->assertSame("0.03330000", $sentData->getMessagePrice());
        $this->assertSame("12345", $sentData->getNetwork());
        $this->assertSame("3.14159265", $sentData->getRemainingBalance());
        $this->assertSame(0, $sentData->getStatus());
        $this->assertGreaterThanOrEqual(2, $end - $start);
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
            $this->assertRequestJsonBodyContains('to', $args['to'], $request);
            $this->assertRequestJsonBodyContains('from', $args['from'], $request);
            $this->assertRequestJsonBodyContains('text', $args['text'], $request);

            return true;
        }))->willReturn($this->getResponse('multi'));

        $response = $this->smsClient->send((new SMS($args['to'], $args['from'], $args['text'])));
        $rawData = json_decode($this->getResponse('multi')->getBody()->getContents(), true);

        $this->assertCount((int)$rawData['message-count'], $response);

        foreach ($response as $key => $sentData) {
            $this->assertSame($rawData['messages'][$key]['to'], $sentData->getTo());
            $this->assertSame($rawData['messages'][$key]['message-id'], $sentData->getMessageId());
            $this->assertSame($rawData['messages'][$key]['message-price'], $sentData->getMessagePrice());
            $this->assertSame($rawData['messages'][$key]['network'], $sentData->getNetwork());
            $this->assertSame($rawData['messages'][$key]['remaining-balance'], $sentData->getRemainingBalance());
            $this->assertSame((int)$rawData['messages'][$key]['status'], $sentData->getStatus());
        }
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanSend2FAMessage(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestJsonBodyContains('to', '447700900000', $request);
            $this->assertRequestJsonBodyContains('pin', 1245, $request);

            return true;
        }))->willReturn($this->getResponse('send-success'));

        $sentData = $this->smsClient->sendTwoFactor('447700900000', 1245);

        $this->assertSame('447700900000', $sentData->getTo());
        $this->assertSame('0A0000000123ABCD1', $sentData->getMessageId());
        $this->assertSame("0.03330000", $sentData->getMessagePrice());
        $this->assertSame("12345", $sentData->getNetwork());
        $this->assertSame("3.14159265", $sentData->getRemainingBalance());
        $this->assertSame(0, $sentData->getStatus());
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

    public function testLogsWarningWhenSendingUnicodeAsText(): void
    {
        $this->vonageClient->send(Argument::that(fn(Request $request) => true))->willReturn($this->getResponse('send-success'));

        $args = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => "This needs to be sent as unicode これはユニコード",
            'account-ref' => 'customer1234',
            'client-ref' => 'my-personal-reference'
        ];

        $message = (new SMS($args['to'], $args['from'], $args['text']))
            ->setClientRef($args['client-ref'])
            ->setAccountRef($args['account-ref']);

        $logger = $this->prophesize(LoggerInterface::class);

        $logger->log(
            Argument::that(function ($level) {
                $this->assertEquals('warning', $level);
                return true;
            }),
            Argument::that(function ($message) {
                $this->assertEquals('You are sending a message as `text` which contains non-GSM7 
            characters. This could result in encoding problems with the target device - See 
            https://developer.vonage.com/messaging/sms for details, or email support@vonage.com if you have any 
            questions.', $message);
                return true;
            }),
            Argument::any()
        );

        $this->smsClient->setLogger($logger->reveal());

        $response = $this->smsClient->send($message);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanSendAlert(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestJsonBodyContains('to', '447700900000', $request);
            $this->assertRequestJsonBodyContains('key', 'value', $request);

            return true;
        }))->willReturn($this->getResponse('send-success'));

        $response = $this->smsClient->sendAlert('447700900000', ['key' => 'value']);
        $sentData = $response->current();

        $this->assertCount(1, $response);
        $this->assertSame('447700900000', $sentData->getTo());
        $this->assertSame('0A0000000123ABCD1', $sentData->getMessageId());
        $this->assertSame("0.03330000", $sentData->getMessagePrice());
        $this->assertSame("12345", $sentData->getNetwork());
        $this->assertSame("3.14159265", $sentData->getRemainingBalance());
        $this->assertSame(0, $sentData->getStatus());
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
}
