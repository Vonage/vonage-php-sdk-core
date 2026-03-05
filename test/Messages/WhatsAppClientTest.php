<?php

namespace VonageTest\Messages;

use Laminas\Diactoros\Request;
use Prophecy\Argument;
use Vonage\Messages\Channel\BaseMessage;
use Vonage\Messages\Channel\WhatsApp\MessageObjects\StickerObject;
use Vonage\Messages\Channel\WhatsApp\WhatsAppAudio;
use Vonage\Messages\Channel\WhatsApp\WhatsAppCustom;
use Vonage\Messages\Channel\WhatsApp\WhatsAppFile;
use Vonage\Messages\Channel\WhatsApp\WhatsAppImage;
use Vonage\Messages\Channel\WhatsApp\WhatsAppSticker;
use Vonage\Messages\Channel\WhatsApp\WhatsAppTemplate;
use Vonage\Messages\Channel\WhatsApp\WhatsAppText;
use Vonage\Messages\Channel\WhatsApp\WhatsAppVideo;
use Vonage\Messages\Client as MessagesClient;
use Vonage\Messages\MessageObjects\AudioObject;
use Vonage\Messages\MessageObjects\FileObject;
use Vonage\Messages\MessageObjects\ImageObject;
use Vonage\Messages\MessageObjects\TemplateObject;
use Vonage\Messages\MessageObjects\VideoObject;

class WhatsAppClientTest extends MessagesClientTest
{
    public function stickerTypeProvider(): array
    {
        return [
            ['url', 'https://example.com', true],
            ['id', 'ds87g8-ds9g8s098-asdhj8-dsifug8', true],
            ['caption', 'this is not valid', false]
        ];
    }

    public function testCanSendWhatsAppText(): void
    {
        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => 'This is a WhatsApp text'
        ];

        $message = new WhatsAppText($payload['to'], $payload['from'], $payload['text']);
        $message->setContext(['message_uuid' => 'a1b2c3d4a1b2c3d4']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('text', $payload['text'], $request);
            $this->assertRequestJsonBodyContains('channel', 'whatsapp', $request);
            $this->assertRequestJsonBodyContains('message_type', 'text', $request);
            $this->assertRequestJsonBodyContains('context', ['message_uuid' => 'a1b2c3d4a1b2c3d4'], $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendWhatsAppTextWithoutContext(): void
    {
        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => 'This is a WhatsApp text'
        ];

        $message = new WhatsAppText($payload['to'], $payload['from'], $payload['text']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('text', $payload['text'], $request);
            $this->assertRequestJsonBodyContains('channel', 'whatsapp', $request);
            $this->assertRequestJsonBodyContains('message_type', 'text', $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
    }

    public function testCanSendWhatsAppImage(): void
    {
        $imageUrl = 'https://picsum.photos/200/300';
        $whatsAppImageObject = new ImageObject($imageUrl, 'Picture of a skateboarder');

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'image' => $whatsAppImageObject
        ];

        $message = new WhatsAppImage($payload['to'], $payload['from'], $whatsAppImageObject);
        $message->setContext(['message_uuid' => 'a1b2c3d4a1b2c3d4']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('image', $payload['image']->toArray(), $request);
            $this->assertRequestJsonBodyContains('channel', 'whatsapp', $request);
            $this->assertRequestJsonBodyContains('message_type', 'image', $request);
            $this->assertRequestJsonBodyContains('context', ['message_uuid' => 'a1b2c3d4a1b2c3d4'], $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendWhatsAppAudio(): void
    {
        $audioObject = new AudioObject(
            'https://file-examples.com/wp-content/uploads/2017/11/file_example_MP3_700KB.mp3',
            null
        );

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'audio' => $audioObject
        ];

        $message = new WhatsAppAudio($payload['to'], $payload['from'], $audioObject);
        $message->setContext(['message_uuid' => 'a1b2c3d4a1b2c3d4']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('audio', $payload['audio']->toArray(), $request);
            $this->assertRequestJsonBodyContains('channel', 'whatsapp', $request);
            $this->assertRequestJsonBodyContains('message_type', 'audio', $request);
            $this->assertRequestJsonBodyContains('context', ['message_uuid' => 'a1b2c3d4a1b2c3d4'], $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendWhatsAppVideo(): void
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

        $message = new WhatsAppVideo($payload['to'], $payload['from'], $videoObject);
        $message->setContext(['message_uuid' => 'a1b2c3d4a1b2c3d4']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('video', $payload['video']->toArray(), $request);
            $this->assertRequestJsonBodyContains('channel', 'whatsapp', $request);
            $this->assertRequestJsonBodyContains('message_type', 'video', $request);
            $this->assertRequestJsonBodyContains('context', ['message_uuid' => 'a1b2c3d4a1b2c3d4'], $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendWhatsAppFile(): void
    {
        $fileObject = new FileObject(
            'https://file-examples.com/wp-content/uploads/2017/04/file_example_MP4_480_1_5MG.mp4',
            'some file',
            'reticulating splines'
        );

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'file' => $fileObject
        ];

        $message = new WhatsAppFile($payload['to'], $payload['from'], $fileObject);
        $message->setContext(['message_uuid' => 'a1b2c3d4a1b2c3d4']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('file', $payload['file']->toArray(), $request);
            $this->assertRequestJsonBodyContains('channel', 'whatsapp', $request);
            $this->assertRequestJsonBodyContains('message_type', 'file', $request);
            $this->assertRequestJsonBodyContains('context', ['message_uuid' => 'a1b2c3d4a1b2c3d4'], $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendWhatsAppTemplate(): void
    {
        $templateObject = new TemplateObject(
            'verify',
            ['key' => 'value']
        );

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'template' => $templateObject
        ];

        $message = new WhatsAppTemplate($payload['to'], $payload['from'], $templateObject, 'en_GB');
        $message->setContext(['message_uuid' => 'a1b2c3d4a1b2c3d4']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('template', $payload['template']->toArray(), $request);
            $this->assertRequestJsonBodyContains('channel', 'whatsapp', $request);
            $this->assertRequestJsonBodyContains('message_type', 'template', $request);
            $this->assertRequestJsonBodyContains('context', ['message_uuid' => 'a1b2c3d4a1b2c3d4'], $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    /**
     * @dataProvider stickerTypeProvider
     */
    public function testCanSendWhatsAppSticker($type, $value, $valid): void
    {
        if (!$valid) {
            $this->expectException(\InvalidArgumentException::class);
        }

        $stickerObject = new StickerObject(
            $type,
            $value
        );

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'sticker' => $stickerObject
        ];

        $message = new WhatsAppSticker($payload['to'], $payload['from'], $stickerObject);
        $message->setContext(['message_uuid' => 'a1b2c3d4a1b2c3d4']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload, $type) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('channel', 'whatsapp', $request);
            $this->assertRequestJsonBodyContains('message_type', BaseMessage::MESSAGES_SUBTYPE_STICKER, $request);
            $this->assertRequestJsonBodyContains('context', ['message_uuid' => 'a1b2c3d4a1b2c3d4'], $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendWhatsAppCustom(): void
    {
        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'custom' => [
                'type' => 'template'
            ]
        ];

        $message = new WhatsAppCustom($payload['to'], $payload['from'], $payload['custom']);
        $message->setContext(['message_uuid' => 'a1b2c3d4a1b2c3d4']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('custom', $payload['custom'], $request);
            $this->assertRequestJsonBodyContains('channel', 'whatsapp', $request);
            $this->assertRequestJsonBodyContains('message_type', 'custom', $request);
            $this->assertRequestJsonBodyContains('context', ['message_uuid' => 'a1b2c3d4a1b2c3d4'], $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanUpdateWhatsAppStatus(): void
    {
        $geoSpecificClient = clone $this->messageClient;
        $geoSpecificClient->getAPIResource()->setBaseUrl('https://api-us.nexmo.com/v1');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertEquals(
                'Bearer ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 7)
            );
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-us.nexmo.com/v1/messages/6ce72c29-e454-442a-94f2-47a1cadba45f',
                $uriString
            );

            $this->assertRequestJsonBodyContains('status', 'read', $request);
            $this->assertEquals('PATCH', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('rcs-update-success'));

        $geoSpecificClient->markAsStatus('6ce72c29-e454-442a-94f2-47a1cadba45f', MessagesClient::WHATSAPP_STATUS_READ);
    }
}
