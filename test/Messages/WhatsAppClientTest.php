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

        $capturedRequest = null;
        $this->vonageClient->send(Argument::that(function (Request $request) use (&$capturedRequest) {
            $capturedRequest = $request;
            return true;
        }))->willReturn($this->getResponse('sms-success', 202));

        $result = $this->messageClient->send($message);

        $this->assertNotNull($capturedRequest, 'No HTTP request was sent');
        $this->assertEquals('POST', $capturedRequest->getMethod());
        $this->assertRequestJsonBodyContains('to', $payload['to'], $capturedRequest);
        $this->assertRequestJsonBodyContains('from', $payload['from'], $capturedRequest);
        $this->assertRequestJsonBodyContains('text', $payload['text'], $capturedRequest);
        $this->assertRequestJsonBodyContains('channel', 'whatsapp', $capturedRequest);
        $this->assertRequestJsonBodyContains('message_type', 'text', $capturedRequest);
        $this->assertRequestJsonBodyContains('context', ['message_uuid' => 'a1b2c3d4a1b2c3d4'], $capturedRequest);
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

        $capturedRequest = null;
        $this->vonageClient->send(Argument::that(function (Request $request) use (&$capturedRequest) {
            $capturedRequest = $request;
            return true;
        }))->willReturn($this->getResponse('sms-success', 202));

        $result = $this->messageClient->send($message);

        $this->assertNotNull($capturedRequest, 'No HTTP request was sent');
        $this->assertEquals('POST', $capturedRequest->getMethod());
        $this->assertRequestJsonBodyContains('to', $payload['to'], $capturedRequest);
        $this->assertRequestJsonBodyContains('from', $payload['from'], $capturedRequest);
        $this->assertRequestJsonBodyContains('text', $payload['text'], $capturedRequest);
        $this->assertRequestJsonBodyContains('channel', 'whatsapp', $capturedRequest);
        $this->assertRequestJsonBodyContains('message_type', 'text', $capturedRequest);
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

        $capturedRequest = null;
        $this->vonageClient->send(Argument::that(function (Request $request) use (&$capturedRequest) {
            $capturedRequest = $request;
            return true;
        }))->willReturn($this->getResponse('sms-success', 202));

        $result = $this->messageClient->send($message);

        $this->assertNotNull($capturedRequest, 'No HTTP request was sent');
        $this->assertEquals('POST', $capturedRequest->getMethod());
        $this->assertRequestJsonBodyContains('to', $payload['to'], $capturedRequest);
        $this->assertRequestJsonBodyContains('from', $payload['from'], $capturedRequest);
        $this->assertRequestJsonBodyContains('image', $payload['image']->toArray(), $capturedRequest);
        $this->assertRequestJsonBodyContains('channel', 'whatsapp', $capturedRequest);
        $this->assertRequestJsonBodyContains('message_type', 'image', $capturedRequest);
        $this->assertRequestJsonBodyContains('context', ['message_uuid' => 'a1b2c3d4a1b2c3d4'], $capturedRequest);
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

        $capturedRequest = null;
        $this->vonageClient->send(Argument::that(function (Request $request) use (&$capturedRequest) {
            $capturedRequest = $request;
            return true;
        }))->willReturn($this->getResponse('sms-success', 202));

        $result = $this->messageClient->send($message);

        $this->assertNotNull($capturedRequest, 'No HTTP request was sent');
        $this->assertEquals('POST', $capturedRequest->getMethod());
        $this->assertRequestJsonBodyContains('to', $payload['to'], $capturedRequest);
        $this->assertRequestJsonBodyContains('from', $payload['from'], $capturedRequest);
        $this->assertRequestJsonBodyContains('audio', $payload['audio']->toArray(), $capturedRequest);
        $this->assertRequestJsonBodyContains('channel', 'whatsapp', $capturedRequest);
        $this->assertRequestJsonBodyContains('message_type', 'audio', $capturedRequest);
        $this->assertRequestJsonBodyContains('context', ['message_uuid' => 'a1b2c3d4a1b2c3d4'], $capturedRequest);
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

        $capturedRequest = null;
        $this->vonageClient->send(Argument::that(function (Request $request) use (&$capturedRequest) {
            $capturedRequest = $request;
            return true;
        }))->willReturn($this->getResponse('sms-success', 202));

        $result = $this->messageClient->send($message);

        $this->assertNotNull($capturedRequest, 'No HTTP request was sent');
        $this->assertEquals('POST', $capturedRequest->getMethod());
        $this->assertRequestJsonBodyContains('to', $payload['to'], $capturedRequest);
        $this->assertRequestJsonBodyContains('from', $payload['from'], $capturedRequest);
        $this->assertRequestJsonBodyContains('video', $payload['video']->toArray(), $capturedRequest);
        $this->assertRequestJsonBodyContains('channel', 'whatsapp', $capturedRequest);
        $this->assertRequestJsonBodyContains('message_type', 'video', $capturedRequest);
        $this->assertRequestJsonBodyContains('context', ['message_uuid' => 'a1b2c3d4a1b2c3d4'], $capturedRequest);
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

        $capturedRequest = null;
        $this->vonageClient->send(Argument::that(function (Request $request) use (&$capturedRequest) {
            $capturedRequest = $request;
            return true;
        }))->willReturn($this->getResponse('sms-success', 202));

        $result = $this->messageClient->send($message);

        $this->assertNotNull($capturedRequest, 'No HTTP request was sent');
        $this->assertEquals('POST', $capturedRequest->getMethod());
        $this->assertRequestJsonBodyContains('to', $payload['to'], $capturedRequest);
        $this->assertRequestJsonBodyContains('from', $payload['from'], $capturedRequest);
        $this->assertRequestJsonBodyContains('file', $payload['file']->toArray(), $capturedRequest);
        $this->assertRequestJsonBodyContains('channel', 'whatsapp', $capturedRequest);
        $this->assertRequestJsonBodyContains('message_type', 'file', $capturedRequest);
        $this->assertRequestJsonBodyContains('context', ['message_uuid' => 'a1b2c3d4a1b2c3d4'], $capturedRequest);
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

        $capturedRequest = null;
        $this->vonageClient->send(Argument::that(function (Request $request) use (&$capturedRequest) {
            $capturedRequest = $request;
            return true;
        }))->willReturn($this->getResponse('sms-success', 202));

        $result = $this->messageClient->send($message);

        $this->assertNotNull($capturedRequest, 'No HTTP request was sent');
        $this->assertEquals('POST', $capturedRequest->getMethod());
        $this->assertRequestJsonBodyContains('to', $payload['to'], $capturedRequest);
        $this->assertRequestJsonBodyContains('from', $payload['from'], $capturedRequest);
        $this->assertRequestJsonBodyContains('template', $payload['template']->toArray(), $capturedRequest);
        $this->assertRequestJsonBodyContains('channel', 'whatsapp', $capturedRequest);
        $this->assertRequestJsonBodyContains('message_type', 'template', $capturedRequest);
        $this->assertRequestJsonBodyContains('context', ['message_uuid' => 'a1b2c3d4a1b2c3d4'], $capturedRequest);
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

        $capturedRequest = null;
        $this->vonageClient->send(Argument::that(function (Request $request) use (&$capturedRequest) {
            $capturedRequest = $request;
            return true;
        }))->willReturn($this->getResponse('sms-success', 202));

        $result = $this->messageClient->send($message);

        $this->assertNotNull($capturedRequest, 'No HTTP request was sent');
        $this->assertEquals('POST', $capturedRequest->getMethod());
        $this->assertRequestJsonBodyContains('to', $payload['to'], $capturedRequest);
        $this->assertRequestJsonBodyContains('from', $payload['from'], $capturedRequest);
        $this->assertRequestJsonBodyContains('channel', 'whatsapp', $capturedRequest);
        $this->assertRequestJsonBodyContains('message_type', BaseMessage::MESSAGES_SUBTYPE_STICKER, $capturedRequest);
        $this->assertRequestJsonBodyContains('context', ['message_uuid' => 'a1b2c3d4a1b2c3d4'], $capturedRequest);
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

        $capturedRequest = null;
        $this->vonageClient->send(Argument::that(function (Request $request) use (&$capturedRequest) {
            $capturedRequest = $request;
            return true;
        }))->willReturn($this->getResponse('sms-success', 202));

        $result = $this->messageClient->send($message);

        $this->assertNotNull($capturedRequest, 'No HTTP request was sent');
        $this->assertEquals('POST', $capturedRequest->getMethod());
        $this->assertRequestJsonBodyContains('to', $payload['to'], $capturedRequest);
        $this->assertRequestJsonBodyContains('from', $payload['from'], $capturedRequest);
        $this->assertRequestJsonBodyContains('custom', $payload['custom'], $capturedRequest);
        $this->assertRequestJsonBodyContains('channel', 'whatsapp', $capturedRequest);
        $this->assertRequestJsonBodyContains('message_type', 'custom', $capturedRequest);
        $this->assertRequestJsonBodyContains('context', ['message_uuid' => 'a1b2c3d4a1b2c3d4'], $capturedRequest);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanUpdateWhatsAppStatus(): void
    {
        $geoSpecificClient = clone $this->messageClient;
        $geoSpecificClient->getAPIResource()->setBaseUrl('https://api-us.nexmo.com/v1');

        $capturedRequest = null;
        $this->vonageClient->send(Argument::that(function (Request $request) use (&$capturedRequest) {
            $capturedRequest = $request;
            return true;
        }))->willReturn($this->getResponse('rcs-update-success'));

        $geoSpecificClient->markAsStatus('6ce72c29-e454-442a-94f2-47a1cadba45f', MessagesClient::WHATSAPP_STATUS_READ);

        $this->assertNotNull($capturedRequest, 'No HTTP request was sent');
        $this->assertEquals('PATCH', $capturedRequest->getMethod());
        $this->assertEquals(
            'https://api-us.nexmo.com/v1/6ce72c29-e454-442a-94f2-47a1cadba45f',
            (string) $capturedRequest->getUri()
        );
        $this->assertEquals('Bearer ', mb_substr($capturedRequest->getHeaders()['Authorization'][0], 0, 7));
        $this->assertRequestJsonBodyContains('status', 'read', $capturedRequest);
    }

    public function testSendTypingIndicators(): void
    {
        $capturedRequest = null;

        $this->vonageClient->send(Argument::that(
            function (Request $request) use (&$capturedRequest) {
                $capturedRequest = $request;
                return true;
            }
        ))->willReturn($this->getResponse('rcs-update-success'));

        $this->messageClient->updateTypingIndicators('6ce72c29-e454-442a-94f2-47a1cadba45f', 'read', true, 'text');

        $this->assertNotNull(
            $capturedRequest,
            'HTTP request was never sent — check that vonageClient->send() is being called'
        );

        $this->assertEquals(
            'PATCH',
            $capturedRequest->getMethod(),
        );

        $this->assertStringContainsString(
            '6ce72c29-e454-442a-94f2-47a1cadba45f',
            (string) $capturedRequest->getUri(),
        );

        $this->assertEquals(
            'Bearer ',
            mb_substr(
                $capturedRequest->getHeaders()['Authorization'][0],
                0,
                7,
            ),
        );

        $this->assertRequestJsonBodyContains(
            'status',
            'read',
            $capturedRequest,
        );

        $this->assertRequestJsonBodyContains(
            'replying_indicator',
            ['show' => true, 'type' => 'text'],
            $capturedRequest,
        );
    }
}
