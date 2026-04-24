<?php

namespace VonageTest\Messages;

use Laminas\Diactoros\Request;
use Prophecy\Argument;
use Vonage\Messages\Channel\Viber\ViberFile;
use Vonage\Messages\Channel\Viber\ViberImage;
use Vonage\Messages\Channel\Viber\ViberText;
use Vonage\Messages\Channel\Viber\ViberVideo;
use Vonage\Messages\MessageObjects\FileObject;
use Vonage\Messages\MessageObjects\ImageObject;
use Vonage\Messages\MessageObjects\VideoObject;

class ViberClientTest extends MessagesClientTest
{
    public function testCanSendViberText(): void
    {
        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => 'This is a viber text',
            'category' => 'transaction',
            'ttl' => 500,
            'type' => 'test_type'
        ];

        $message = new ViberText(
            $payload['to'],
            $payload['from'],
            $payload['text'],
            $payload['category'],
            $payload['ttl'],
            $payload['type']
        );

        $this->httpClient->sendRequest(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('text', $payload['text'], $request);
            $this->assertRequestJsonBodyContains('channel', 'viber_service', $request);
            $this->assertRequestJsonBodyContains('message_type', 'text', $request);
            $this->assertRequestJsonBodyContains(
                'viber_service',
                [
                    'category' => 'transaction',
                    'ttl' => 500,
                    'type' => 'test_type'
                ],
                $request
            );
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendViberTextWithoutViberObject(): void
    {
        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => 'This is a viber text',
        ];

        $message = new ViberText(
            $payload['to'],
            $payload['from'],
            $payload['text']
        );

        $this->httpClient->sendRequest(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('text', $payload['text'], $request);
            $this->assertRequestJsonBodyContains('channel', 'viber_service', $request);
            $this->assertRequestJsonBodyContains('message_type', 'text', $request);
            $this->assertRequestJsonBodyMissing('viber_service', $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendViberImage(): void
    {
        $imageUrl = 'https://picsum.photos/200/300';
        $imageObject = new ImageObject($imageUrl, 'Picture of a skateboarder');

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'image' => $imageObject,
            'category' => 'transaction',
            'ttl' => 500,
            'type' => 'test_type'
        ];

        $message = new ViberImage(
            $payload['to'],
            $payload['from'],
            $imageObject,
            $payload['category'],
            $payload['ttl'],
            $payload['type']
        );

        $this->httpClient->sendRequest(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('image', $payload['image']->toArray(), $request);
            $this->assertRequestJsonBodyContains('channel', 'viber_service', $request);
            $this->assertRequestJsonBodyContains('message_type', 'image', $request);

            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendViberFile(): void
    {
        $fileObject = new FileObject(
            'https://file-examples.com/wp-content/uploads/2017/04/file_example_MP4_480_1_5MG.mp4',
            'some file'
        );

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'file' => $fileObject
        ];

        $message = new ViberFile(
            $payload['to'],
            $payload['from'],
            $payload['file']
        );

        $this->httpClient->sendRequest(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('file', $payload['file']->toArray(), $request);
            $this->assertRequestJsonBodyContains('channel', 'viber_service', $request);
            $this->assertRequestJsonBodyContains('message_type', 'file', $request);

            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendViberVideo(): void
    {
        $videoObject = new VideoObject(
            'https://file-examples.com/wp-content/uploads/2017/04/file_example_MP4_480_1_5MG.mp4',
            'some video'
        );

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'video' => $videoObject,
            'thumb' => 'https://file-examples.com/wp-content/uploads/2017/04/preview.jpg',
            'duration' => '30',
            'file_size' => '5'
        ];

        $message = new ViberVideo(
            $payload['to'],
            $payload['from'],
            $payload['thumb'],
            $payload['video'],
            $payload['duration'],
            $payload['file_size']
        );

        $videoMatch = $videoObject->toArray();
        $videoMatch['thumb_url'] = $payload['thumb'];

        $serviceObject = [
            'duration' => $payload['duration'],
            'file_size' => $payload['file_size']
        ];

        $this->httpClient->sendRequest(Argument::that(function (Request $request) use ($payload, $videoMatch, $serviceObject) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('video', $videoMatch, $request);
            $this->assertRequestJsonBodyContains('channel', 'viber_service', $request);
            $this->assertRequestJsonBodyContains('message_type', 'video', $request);
            $this->assertRequestJsonBodyContains('viber_service', $serviceObject, $request);

            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }
}
