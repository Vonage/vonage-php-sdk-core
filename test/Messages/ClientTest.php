<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Messages;

use Prophecy\Prophecy\ObjectProphecy;
use Vonage\Client\APIResource;
use Vonage\Messages\MessageType\Messenger;
use Vonage\Messages\MessageType\MMS;
use Vonage\Messages\MessageType\SMS;
use Vonage\Messages\MessageType\Viber;
use Vonage\Messages\MessageType\WhatsApp;
use Vonage\SMS\ExceptionErrorHandler;
use VonageTest\VonageTestCase;
use Vonage\Client;
use Vonage\Messages\Client as MessagesClient;

class ClientTest extends VonageTestCase
{
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
            ->setErrorsOn200(true)
            ->setClient($this->vonageClient->reveal())
            ->setExceptionErrorHandler(new ExceptionErrorHandler())
            ->setBaseUrl('https://rest.nexmo.com');

        $this->messageClient = new MessagesClient($this->api);
    }

    public function testHasSetupClientCorrectly(): void
    {
        $this->assertInstanceOf(APIResource::class, $this->api);
        $this->assertInstanceOf(MessagesClient::class, $this->messageClient);
    }

    public function testCanSendDefaultSMS(): void
    {
        $message = new SMS();
        $this->markTestIncomplete('To write');
    }

    public function testCanSendDefaultWhatsApp(): void
    {
        $message = new WhatsApp();
        $this->markTestIncomplete('To write');
    }

    public function testCanSendDefaultMMS(): void
    {
        $message = new MMS();
        $this->markTestIncomplete('To write');
    }

    public function testCanSendDefaultMessenger(): void
    {
        $message = new Messenger();
        $this->markTestIncomplete('To write');
    }

    public function testCanSendDefaultViber(): void
    {
        $message = new Viber();
        $this->markTestIncomplete('To write');
    }
}
