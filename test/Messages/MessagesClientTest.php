<?php

namespace VonageTest\Messages;

use Laminas\Diactoros\Request;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use VonageTest\Traits\HTTPTestTrait;
use VonageTest\Traits\Psr7AssertionTrait;
use VonageTest\VonageTestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Messages\Channel\SMS\SMSText;
use Vonage\Messages\Client as MessagesClient;
use Vonage\Messages\ExceptionErrorHandler;

class MessagesClientTest extends VonageTestCase
{
    use Psr7AssertionTrait;
    use HTTPTestTrait;

    protected ObjectProphecy $vonageClient;
    protected $httpClient;
    protected MessagesClient $messageClient;
    protected APIResource $api;

    /**
     * Create the Message API Client, and mock the Vonage Client
     */
    public function setUp(): void
    {
        $this->responsesDirectory = __DIR__ . '/Fixtures/Responses';

        $this->vonageClient = $this->prophesize(Client::class);
        $this->httpClient = $this->prophesize(\Psr\Http\Client\ClientInterface::class);
        $this->vonageClient->getHttpClient()->willReturn($this->httpClient->reveal());
        $this->vonageClient->getRestUrl()->willReturn('https://rest.nexmo.com');
        $this->vonageClient->getCredentials()->willReturn(
            new Client\Credentials\Container(new Client\Credentials\Keypair(
                file_get_contents(__DIR__ . '/../Client/Credentials/test.key'),
                'def'
            ))
        );

        /** @noinspection PhpParamsInspection */
        $this->api = (new APIResource($this->vonageClient->reveal()))
            ->setCollectionName('messages')
            ->setIsHAL(false)
            ->setErrorsOn200(false)
            ->setAuthHandlers([new Client\Credentials\Handler\KeypairHandler(), new Client\Credentials\Handler\BasicHandler()])
            ->setExceptionErrorHandler(new ExceptionErrorHandler())
            ->setBaseUrl('https://rest.nexmo.com');

        $this->messageClient = new MessagesClient($this->api);
    }

    public function testHasSetupClientCorrectly(): void
    {
        $this->assertInstanceOf(MessagesClient::class, $this->messageClient);
    }

    public function testThrowsRequestErrorOnBadRequest(): void
    {
        $this->expectException(Client\Exception\Request::class);
        $this->expectExceptionMessage('The request body did not contain valid JSON: Unexpected character (\'"\' (code 34)): was expecting comma to separate Object entries');

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => '\\{text: "reticulating splines"}'
        ];

        $message = new SMSText($payload['to'], $payload['from'], $payload['text']);

        $this->httpClient->sendRequest(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('text', $payload['text'], $request);
            $this->assertRequestJsonBodyContains('channel', 'sms', $request);
            $this->assertRequestJsonBodyContains('message_type', 'text', $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('bad-request', 422));
        $this->messageClient->send($message);
    }

    public function testThrowsRateLimit(): void
    {
        $this->expectException(Client\Exception\ThrottleException::class);
        $this->expectExceptionMessage('Rate Limit Hit: Please wait, then retry your request');

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => 'Reticulating Splines'
        ];

        $message = new SMSText($payload['to'], $payload['from'], $payload['text']);

        $this->httpClient->sendRequest(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('text', $payload['text'], $request);
            $this->assertRequestJsonBodyContains('channel', 'sms', $request);
            $this->assertRequestJsonBodyContains('message_type', 'text', $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('rate-limit', 429));
        $this->messageClient->send($message);
    }
}
