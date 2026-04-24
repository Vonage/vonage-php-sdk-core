<?php

namespace VonageTest\Messages;

use Laminas\Diactoros\Request;
use Prophecy\Argument;
use Vonage\Messages\Channel\MMS\MMSContent;
use Vonage\Messages\Channel\MMS\MMSText;
use Vonage\Messages\Channel\MMS\MMSAudio;
use Vonage\Messages\Channel\MMS\MMSFile;
use Vonage\Messages\Channel\MMS\MMSImage;
use Vonage\Messages\Channel\MMS\MMSvCard;
use Vonage\Messages\Channel\MMS\MMSVideo;
use Vonage\Messages\MessageObjects\AudioObject;
use Vonage\Messages\MessageObjects\ContentObject;
use Vonage\Messages\MessageObjects\FileObject;
use Vonage\Messages\MessageObjects\ImageObject;
use Vonage\Messages\MessageObjects\VCardObject;
use Vonage\Messages\MessageObjects\VideoObject;

class MMSClientTest extends MessagesClientTest
{
    public function testCanSendMMSContent(): void
    {
        $contentUrl = 'https://picsum.photos/200/300';
        $mmsContentObject = new ContentObject($contentUrl, 'Picture of a skateboarder', ContentObject::TYPE_IMAGE);

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'content' => $mmsContentObject
        ];

        $message = new MMSContent($payload['to'], $payload['from'], $mmsContentObject);
        $message->setTtl(400);

        $this->httpClient->sendRequest(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('content', $payload['content']->toArray(), $request);
            $this->assertRequestJsonBodyContains('channel', 'mms', $request);
            $this->assertRequestJsonBodyContains('message_type', 'content', $request);
            $this->assertRequestJsonBodyContains('ttl', 400, $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendMMSText(): void
    {
        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => 'my cool message'
        ];

        $message = new MMSText($payload['to'], $payload['from'], $payload['text']);
        $message->setTtl(400);

        $this->httpClient->sendRequest(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('text', $payload['text'], $request);
            $this->assertRequestJsonBodyContains('channel', 'mms', $request);
            $this->assertRequestJsonBodyContains('message_type', 'text', $request);
            $this->assertRequestJsonBodyContains('ttl', 400, $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendMMSFile(): void
    {
        $fileUrl = 'https://picsum.photos/200/300';
        $mmsFileObject = new FileObject($fileUrl, 'Picture of a skateboarder');

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'file' => $mmsFileObject
        ];

        $message = new MMSFile($payload['to'], $payload['from'], $mmsFileObject);
        $message->setTtl(400);

        $this->httpClient->sendRequest(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('file', $payload['file']->toArray(), $request);
            $this->assertRequestJsonBodyContains('channel', 'mms', $request);
            $this->assertRequestJsonBodyContains('message_type', 'file', $request);
            $this->assertRequestJsonBodyContains('ttl', 400, $request);
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
        $message->setTtl(400);

        $this->httpClient->sendRequest(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('image', $payload['image']->toArray(), $request);
            $this->assertRequestJsonBodyContains('channel', 'mms', $request);
            $this->assertRequestJsonBodyContains('message_type', 'image', $request);
            $this->assertRequestJsonBodyContains('ttl', 400, $request);
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
        $message->setTtl(400);

        $this->httpClient->sendRequest(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('vcard', $payload['vcard']->toArray(), $request);
            $this->assertRequestJsonBodyContains('channel', 'mms', $request);
            $this->assertRequestJsonBodyContains('message_type', 'vcard', $request);
            $this->assertRequestJsonBodyContains('ttl', 400, $request);

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
        $message->setTtl(400);

        $this->httpClient->sendRequest(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('audio', $payload['audio']->toArray(), $request);
            $this->assertRequestJsonBodyContains('channel', 'mms', $request);
            $this->assertRequestJsonBodyContains('message_type', 'audio', $request);
            $this->assertRequestJsonBodyContains('ttl', 400, $request);
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
        $message->setTtl(400);

        $this->httpClient->sendRequest(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('video', $payload['video']->toArray(), $request);
            $this->assertRequestJsonBodyContains('channel', 'mms', $request);
            $this->assertRequestJsonBodyContains('message_type', 'video', $request);
            $this->assertRequestJsonBodyContains('ttl', 400, $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }
}
