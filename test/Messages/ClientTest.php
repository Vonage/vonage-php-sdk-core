<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Messages;

use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Vonage\Client\APIResource;
use Vonage\Messages\MessageType\SMS\SMSText;
use Vonage\SMS\ExceptionErrorHandler;
use VonageTest\Psr7AssertionTrait;
use VonageTest\VonageTestCase;
use Vonage\Client;
use Vonage\Messages\Client as MessagesClient;

class ClientTest extends VonageTestCase
{
    use Psr7AssertionTrait;

    protected ObjectProphecy $vonageClient;
    protected MessagesClient $messageClient;
    protected APIResource $api;

    /**
     * Create the Message API Client, and mock the Vonage Client
     */
    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getRestUrl()->willReturn('https://rest.nexmo.com');

        /** @noinspection PhpParamsInspection */
        $this->api = (new APIResource())
            ->setCollectionName('messages')
            ->setIsHAL(false)
            ->setErrorsOn200(false)
            ->setClient($this->vonageClient->reveal())
            ->setExceptionErrorHandler(new ExceptionErrorHandler())
            ->setBaseUrl('https://rest.nexmo.com');

        $this->messageClient = new MessagesClient($this->api);
    }

    public function testHasSetupClientCorrectly(): void
    {
        $this->assertInstanceOf(MessagesClient::class, $this->messageClient);
    }

    public function testCanSendSMS(): void
    {
        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => 'Reticulating Splines'
        ];

        $message = new SMSText($payload['to'], $payload['from'], $payload['text']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('text', $payload['text'], $request);

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendWhatsApp(): void
    {
        $this->markTestIncomplete('To write');
    }

    public function testCanSendMMS(): void
    {
        $this->markTestIncomplete('To write');
    }

    public function testCanSendMessenger(): void
    {
        $this->markTestIncomplete('To write');
    }

    public function testCanSendViber(): void
    {
        $this->markTestIncomplete('To write');
    }

    /**
     * This method gets the fixtures and wraps them in a Response object to mock the API
     */
    protected function getResponse(string $identifier, int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/Fixtures/Responses/' . $identifier . '.json', 'rb'), $status);
    }
}
