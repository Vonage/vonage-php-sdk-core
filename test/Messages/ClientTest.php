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
use Vonage\Messages\MessageObjects\AudioObject;
use Vonage\Messages\MessageObjects\ImageObject;
use Vonage\Messages\MessageObjects\VCardObject;
use Vonage\Messages\MessageObjects\VideoObject;
use Vonage\Messages\MessageType\MMS\MMSAudio;
use Vonage\Messages\MessageType\MMS\MMSImage;
use Vonage\Messages\MessageType\MMS\MMSvCard;
use Vonage\Messages\MessageType\MMS\MMSVideo;
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
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendMMSImage(): void
    {
        $imageUrl = 'https://picsum.photos/200/300';
        $mmsImageObject = new ImageObject($imageUrl, 'Picture of a skateboarder');

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'image' => $mmsImageObject
        ];

        $message = new MMSImage($payload['to'], $payload['from'], $mmsImageObject);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('image', $payload['image']->toArray(), $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendMMSvCard(): void
    {
        $vCardUrl = 'https://github.com/nuovo/vCard-parser/blob/master/Example.vcf';
        $vCardObject = new VCardObject($vCardUrl);

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'vcard' => $vCardObject
        ];

        $message = new MMSvCard($payload['to'], $payload['from'], $vCardObject);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('vcard', $payload['vcard']->toArray(), $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendMMSAudio(): void
    {
        $audioObject = new AudioObject(
            'https://file-examples.com/wp-content/uploads/2017/11/file_example_MP3_700KB.mp3',
            'some audio'
        );

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'audio' => $audioObject
        ];

        $message = new MMSAudio($payload['to'], $payload['from'], $audioObject);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('audio', $payload['audio']->toArray(), $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendMMSVideo(): void
    {
        $videoObject = new VideoObject(
            'https://file-examples.com/wp-content/uploads/2017/04/file_example_MP4_480_1_5MG.mp4',
            'some video'
        );

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'video' => $videoObject
        ];

        $message = new MMSVideo($payload['to'], $payload['from'], $videoObject);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('video', $payload['video']->toArray(), $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    /**
     * This method gets the fixtures and wraps them in a Response object to mock the API
     */
    protected function getResponse(string $identifier, int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/Fixtures/Responses/' . $identifier . '.json', 'rb'), $status);
    }
}
