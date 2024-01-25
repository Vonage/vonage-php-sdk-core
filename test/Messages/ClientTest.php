<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Messages;

use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Vonage\Client\APIResource;
use Vonage\Messages\Channel\BaseMessage;
use Vonage\Messages\Channel\Viber\MessageObjects\ViberActionObject;
use Vonage\Messages\Channel\Viber\ViberFile;
use Vonage\Messages\Channel\Viber\ViberVideo;
use Vonage\Messages\Channel\WhatsApp\MessageObjects\StickerObject;
use Vonage\Messages\Channel\WhatsApp\WhatsAppSticker;
use Vonage\Messages\ExceptionErrorHandler;
use Vonage\Messages\MessageObjects\AudioObject;
use Vonage\Messages\MessageObjects\FileObject;
use Vonage\Messages\MessageObjects\ImageObject;
use Vonage\Messages\MessageObjects\TemplateObject;
use Vonage\Messages\MessageObjects\VCardObject;
use Vonage\Messages\MessageObjects\VideoObject;
use Vonage\Messages\Channel\Messenger\MessengerAudio;
use Vonage\Messages\Channel\Messenger\MessengerFile;
use Vonage\Messages\Channel\Messenger\MessengerImage;
use Vonage\Messages\Channel\Messenger\MessengerText;
use Vonage\Messages\Channel\Messenger\MessengerVideo;
use Vonage\Messages\Channel\MMS\MMSAudio;
use Vonage\Messages\Channel\MMS\MMSImage;
use Vonage\Messages\Channel\MMS\MMSvCard;
use Vonage\Messages\Channel\MMS\MMSVideo;
use Vonage\Messages\Channel\SMS\SMSText;
use Vonage\Messages\Channel\Viber\ViberImage;
use Vonage\Messages\Channel\Viber\ViberText;
use Vonage\Messages\Channel\WhatsApp\WhatsAppAudio;
use Vonage\Messages\Channel\WhatsApp\WhatsAppCustom;
use Vonage\Messages\Channel\WhatsApp\WhatsAppFile;
use Vonage\Messages\Channel\WhatsApp\WhatsAppImage;
use Vonage\Messages\Channel\WhatsApp\WhatsAppTemplate;
use Vonage\Messages\Channel\WhatsApp\WhatsAppText;
use Vonage\Messages\Channel\WhatsApp\WhatsAppVideo;
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
        $this->vonageClient->getCredentials()->willReturn(
            new Client\Credentials\Container(new Client\Credentials\Keypair(
                file_get_contents(__DIR__ . '/../Client/Credentials/test.key'),
                'def'
            ))
        );

        /** @noinspection PhpParamsInspection */
        $this->api = (new APIResource())
            ->setCollectionName('messages')
            ->setIsHAL(false)
            ->setErrorsOn200(false)
            ->setClient($this->vonageClient->reveal())
            ->setAuthHandler([new Client\Credentials\Handler\KeypairHandler(), new Client\Credentials\Handler\BasicHandler()])
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
            $this->assertEquals(
                'Bearer ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 7)
            );
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('text', $payload['text'], $request);
            $this->assertRequestJsonBodyContains('channel', 'sms', $request);
            $this->assertRequestJsonBodyContains('message_type', 'text', $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendSMSWithOptionalFields(): void
    {
        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => 'Reticulating Splines',
            'encoding_type' => 'auto',
            'content_id' => '1107457532145798767',
            'entity_id' => '1101456324675322134',
            'webhook_url' => 'https://example.com/status',
            'webhook_version' => 'v1',
            'ttl' => 300
        ];

        $message = new SMSText($payload['to'], $payload['from'], $payload['text']);
        $message->setEncodingType($payload['encoding_type']);
        $message->setTtl($payload['ttl']);
        $message->setContentId($payload['content_id']);
        $message->setEntityId($payload['entity_id']);
        $message->setWebhookUrl($payload['webhook_url']);
        $message->setWebhookVersion($payload['webhook_version']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('ttl', $payload['ttl'], $request);
            $this->assertRequestJsonBodyContains('webhook_url', $payload['webhook_url'], $request);
            $this->assertRequestJsonBodyContains('webhook_version', $payload['webhook_version'], $request);
            $smsObject = [
                'encoding_type' => $payload['encoding_type'],
                'content_id' => $payload['content_id'],
                'entity_id' => $payload['entity_id']
            ];

            $this->assertRequestJsonBodyContains('sms', $smsObject, $request);

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
            $this->assertRequestJsonBodyContains('channel', 'mms', $request);
            $this->assertRequestJsonBodyContains('message_type', 'image', $request);
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
        $vCardObject = new VCardObject($vCardUrl, 'this is a picture');

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
            $this->assertRequestJsonBodyContains('channel', 'mms', $request);
            $this->assertRequestJsonBodyContains('message_type', 'vcard', $request);
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
            $this->assertRequestJsonBodyContains('channel', 'mms', $request);
            $this->assertRequestJsonBodyContains('message_type', 'audio', $request);
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
            $this->assertRequestJsonBodyContains('channel', 'mms', $request);
            $this->assertRequestJsonBodyContains('message_type', 'video', $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendWhatsAppText(): void
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
        $this->assertArrayHasKey('message_uuid', $result);
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

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('image', $payload['image']->toArray(), $request);
            $this->assertRequestJsonBodyContains('channel', 'whatsapp', $request);
            $this->assertRequestJsonBodyContains('message_type', 'image', $request);
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
            'some audio'
        );

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'audio' => $audioObject
        ];

        $message = new WhatsAppAudio($payload['to'], $payload['from'], $audioObject);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('audio', $payload['audio']->toArray(), $request);
            $this->assertRequestJsonBodyContains('channel', 'whatsapp', $request);
            $this->assertRequestJsonBodyContains('message_type', 'audio', $request);
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

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('video', $payload['video']->toArray(), $request);
            $this->assertRequestJsonBodyContains('channel', 'whatsapp', $request);
            $this->assertRequestJsonBodyContains('message_type', 'video', $request);
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

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('file', $payload['file']->toArray(), $request);
            $this->assertRequestJsonBodyContains('channel', 'whatsapp', $request);
            $this->assertRequestJsonBodyContains('message_type', 'file', $request);
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

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('template', $payload['template']->toArray(), $request);
            $this->assertRequestJsonBodyContains('channel', 'whatsapp', $request);
            $this->assertRequestJsonBodyContains('message_type', 'template', $request);
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

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload, $type) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('channel', 'whatsapp', $request);
            $this->assertRequestJsonBodyContains('message_type', BaseMessage::MESSAGES_SUBTYPE_STICKER, $request);
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

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('custom', $payload['custom'], $request);
            $this->assertRequestJsonBodyContains('channel', 'whatsapp', $request);
            $this->assertRequestJsonBodyContains('message_type', 'custom', $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendMessengerText(): void
    {
        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => 'This is a messenger response',
            'category' => 'response'
        ];

        $message = new MessengerText($payload['to'], $payload['from'], $payload['text'], $payload['category']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('text', $payload['text'], $request);
            $this->assertRequestJsonBodyContains('channel', 'messenger', $request);
            $this->assertRequestJsonBodyContains('message_type', 'text', $request);
            $this->assertRequestJsonBodyContains('messenger', ['category' => 'response'], $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendMessengerImage(): void
    {
        $imageUrl = 'https://picsum.photos/200/300';
        $whatsAppImageObject = new ImageObject($imageUrl, 'Picture of a skateboarder');

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'image' => $whatsAppImageObject,
            'category' => 'update'
        ];

        $message = new MessengerImage($payload['to'], $payload['from'], $whatsAppImageObject, $payload['category']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('image', $payload['image']->toArray(), $request);
            $this->assertRequestJsonBodyContains('channel', 'messenger', $request);
            $this->assertRequestJsonBodyContains('message_type', 'image', $request);
            $this->assertRequestJsonBodyContains('messenger', ['category' => 'update'], $request);

            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendMessengerAudio(): void
    {
        $audioObject = new AudioObject(
            'https://file-examples.com/wp-content/uploads/2017/11/file_example_MP3_700KB.mp3',
            'some audio'
        );

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'audio' => $audioObject,
            'category' => 'message_tag',
            'tag' => 'conversation'
        ];

        $message = new MessengerAudio(
            $payload['to'],
            $payload['from'],
            $audioObject,
            $payload['category'],
            $payload['tag']
        );

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('audio', $payload['audio']->toArray(), $request);
            $this->assertRequestJsonBodyContains('channel', 'messenger', $request);
            $this->assertRequestJsonBodyContains('message_type', 'audio', $request);
            $this->assertRequestJsonBodyContains(
                'messenger',
                ['category' => 'message_tag', 'tag' => 'conversation'],
                $request
            );

            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendMessengerVideo(): void
    {
        $videoObject = new VideoObject(
            'https://file-examples.com/wp-content/uploads/2017/04/file_example_MP4_480_1_5MG.mp4',
            'some video'
        );

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'video' => $videoObject,
            'category' => 'response'
        ];

        $message = new MessengerVideo($payload['to'], $payload['from'], $videoObject, $payload['category']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('video', $payload['video']->toArray(), $request);
            $this->assertRequestJsonBodyContains('channel', 'messenger', $request);
            $this->assertRequestJsonBodyContains('messenger', ['category' => 'response'], $request);
            $this->assertRequestJsonBodyContains('message_type', 'video', $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendMessengerFile(): void
    {
        $fileObject = new FileObject(
            'https://file-examples.com/wp-content/uploads/2017/04/file_example_MP4_480_1_5MG.mp4',
            'some file'
        );

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'file' => $fileObject,
            'category' => 'response'
        ];

        $message = new MessengerFile($payload['to'], $payload['from'], $fileObject, $payload['category']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('file', $payload['file']->toArray(), $request);
            $this->assertRequestJsonBodyContains('channel', 'messenger', $request);
            $this->assertRequestJsonBodyContains('messenger', ['category' => 'response'], $request);
            $this->assertRequestJsonBodyContains('message_type', 'file', $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

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

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
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

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('text', $payload['text'], $request);
            $this->assertRequestJsonBodyContains('channel', 'sms', $request);
            $this->assertRequestJsonBodyContains('message_type', 'text', $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('bad-request', 422));
        $result = $this->messageClient->send($message);
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

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('text', $payload['text'], $request);
            $this->assertRequestJsonBodyContains('channel', 'sms', $request);
            $this->assertRequestJsonBodyContains('message_type', 'text', $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('rate-limit', 429));
        $result = $this->messageClient->send($message);
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

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
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

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
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

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
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

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload, $videoMatch, $serviceObject) {
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

    /**
     * This method gets the fixtures and wraps them in a Response object to mock the API
     */
    protected function getResponse(string $identifier, int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/Fixtures/Responses/' . $identifier . '.json', 'rb'), $status);
    }

    public function stickerTypeProvider(): array
    {
        return [
            ['url', 'https://example.com', true],
            ['id', 'ds87g8-ds9g8s098-asdhj8-dsifug8', true],
            ['caption', 'this is not valid', false]
        ];
    }
}
