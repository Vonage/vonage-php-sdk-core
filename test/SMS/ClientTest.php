<?php
declare(strict_types=1);

namespace NexmoTest\SMS;

use Nexmo\SMS\Message\SMS;
use Nexmo\SMS\Client;
use Prophecy\Argument;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Nexmo\Client\APIResource;
use PHPUnit\Framework\TestCase;
use Nexmo\Client as NexmoClient;
use NexmoTest\Psr7AssertionTrait;
use Psr\Http\Message\RequestInterface;
use Nexmo\SMS\ExceptionErrorHandler;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    /**
     * @var APIResource
     */
    protected $api;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $nexmoClient;

    /**
     * @var Client
     */
    protected $smsClient;

    public function setUp()
    {
        $this->nexmoClient = $this->prophesize(NexmoClient::class);
        $this->nexmoClient->getRestUrl()->willReturn('https://rest.nexmo.com');

        $this->api = new APIResource();
        $this->api
            ->setCollectionName('messages')
            ->setIsHAL(false)
            ->setErrorsOn200(true)
            ->setClient($this->nexmoClient->reveal())
            ->setExceptionErrorHandler(new ExceptionErrorHandler())
        ;

        $this->smsClient = new Client($this->api);
    }

    public function testCanSendSMS()
    {
        $args = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => "Go To Gino's"
        ];

        $this->nexmoClient->send(Argument::that(function (Request $request) use ($args) {
            $this->assertRequestJsonBodyContains('to', $args['to'], $request);
            $this->assertRequestJsonBodyContains('from', $args['from'], $request);
            $this->assertRequestJsonBodyContains('text', $args['text'], $request);
            return true;
        }))->willReturn($this->getResponse('send-success'));

        $message = new SMS($args['to'], $args['from'], $args['text']);
        $response = $this->smsClient->send($message);
        
        $sentData = $response->current();
        $this->assertCount(1, $response);
        $this->assertSame($args['to'], $sentData->getTo());
        $this->assertSame('0A0000000123ABCD1', $sentData->getMessageId());
        $this->assertSame("0.03330000", $sentData->getMessagePrice());
        $this->assertSame("12345", $sentData->getNetwork());
        $this->assertSame("3.14159265", $sentData->getRemainingBalance());
        $this->assertSame(0, $sentData->getStatus());
    }

    public function testHandlesEmptyResponse()
    {
        $this->expectException(\Nexmo\Client\Exception\Request::class);
        $this->expectExceptionMessage('unexpected response from API');

        $this->nexmoClient
            ->send(Argument::type(RequestInterface::class))
            ->willReturn($this->getResponse('empty'))
        ;

        $this->smsClient->send(new SMS('14845551212', '16105551212', "Go To Gino's"));
    }

    public function testCanParseErrorsAndThrowException()
    {
        $this->expectException(\Nexmo\Client\Exception\Request::class);
        $this->expectExceptionMessage('Missing from param');

        $this->nexmoClient
            ->send(Argument::type(RequestInterface::class))
            ->willReturn($this->getResponse('fail'))
        ;

        $this->smsClient->send(new SMS('14845551212', '16105551212', "Go To Gino's"));
    }

    public function testCanParseServerErrorsAndThrowException()
    {
        $this->expectException(\Nexmo\Client\Exception\Server::class);
        $this->expectExceptionMessage('Server Error');

        $this->nexmoClient
            ->send(Argument::type(RequestInterface::class))
            ->willReturn($this->getResponse('fail-server'))
        ;

        $this->smsClient->send(new SMS('14845551212', '16105551212', "Go To Gino's"));
    }

    public function testCanHandleRateLimitRequests()
    {
        $rate    = $this->getResponse('ratelimit');
        $rate2    = $this->getResponse('ratelimit');
        $success = $this->getResponse('send-success');

        $args = [
            'to' => '447700900000',
            'from' => '1105551334',
            'text' => 'test message'
        ];

        $this->nexmoClient->send(Argument::that(function (Request $request) use ($args) {
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

    public function testCanHandleRateLimitRequestsWithNoDeclaredTimeout()
    {
        $rate    = $this->getResponse('ratelimit-notime');
        $rate2    = $this->getResponse('ratelimit-notime');
        $success = $this->getResponse('send-success');

        $args = [
            'to' => '447700900000',
            'from' => '1105551334',
            'text' => 'test message'
        ];

        $this->nexmoClient->send(Argument::that(function (Request $request) use ($args) {
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

    public function testCanUnderstandMultiMessageResponses()
    {
        $args = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => "This is an incredibly large SMS message This is an incredibly large SMS message This is an incredibly large SMS message This is an incredibly large SMS message This is an incredibly large SMS message"
        ];

        $this->nexmoClient->send(Argument::that(function (Request $request) use ($args) {
            $this->assertRequestJsonBodyContains('to', $args['to'], $request);
            $this->assertRequestJsonBodyContains('from', $args['from'], $request);
            $this->assertRequestJsonBodyContains('text', $args['text'], $request);
            return true;
        }))->willReturn($this->getResponse('multi'));

        $message = new SMS($args['to'], $args['from'], $args['text']);
        $response = $this->smsClient->send($message);
        
        $rawData = json_decode($this->getResponse('multi')->getBody()->getContents(), true);
        $this->assertCount((int) $rawData['message-count'], $response);
        foreach ($response as $key => $sentData) {
            $this->assertSame($rawData['messages'][$key]['to'], $sentData->getTo());
            $this->assertSame($rawData['messages'][$key]['message-id'], $sentData->getMessageId());
            $this->assertSame($rawData['messages'][$key]['message-price'], $sentData->getMessagePrice());
            $this->assertSame($rawData['messages'][$key]['network'], $sentData->getNetwork());
            $this->assertSame($rawData['messages'][$key]['remaining-balance'], $sentData->getRemainingBalance());
            $this->assertSame((int) $rawData['messages'][$key]['status'], $sentData->getStatus());
        }
    }

    public function testCanSend2FAMessage()
    {
        $this->nexmoClient->send(Argument::that(function (Request $request) {
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

    public function testCanHandleMissingShortcodeOn2FA()
    {
        $this->expectException(\Nexmo\Client\Exception\Request::class);
        $this->expectExceptionMessage('Invalid Account for Campaign');
        $this->expectExceptionCode(101);

        $this->nexmoClient
            ->send(Argument::type(RequestInterface::class))
            ->willReturn($this->getResponse('fail-shortcode'))
        ;

        $this->smsClient->sendTwoFactor('447700900000', 1245);
    }

    public function testCanSendAlert()
    {
        $this->nexmoClient->send(Argument::that(function (Request $request) {
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

    public function testCanHandleMissingAlertSetup()
    {
        $this->expectException(\Nexmo\Client\Exception\Request::class);
        $this->expectExceptionMessage('Invalid Account for Campaign');
        $this->expectExceptionCode(101);

        $this->nexmoClient
            ->send(Argument::type(RequestInterface::class))
            ->willReturn($this->getResponse('fail-shortcode'))
        ;

        $this->smsClient->sendAlert('447700900000', ['key' => 'value']);
        
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
