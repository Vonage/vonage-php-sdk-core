<?php

declare(strict_types=1);

namespace VonageTest\Messages;

use Laminas\Diactoros\Request;
use Prophecy\Argument;
use Vonage\Messages\Channel\Messenger\MessengerAudio;
use Vonage\Messages\Channel\Messenger\MessengerFile;
use Vonage\Messages\Channel\Messenger\MessengerImage;
use Vonage\Messages\Channel\Messenger\MessengerText;
use Vonage\Messages\Channel\Messenger\MessengerVideo;
use Vonage\Messages\Channel\RCS\RcsCardObject;
use Vonage\Messages\Channel\RCS\RcsCard;
use Vonage\Messages\Channel\RCS\RcsCarousel;
use Vonage\Messages\Channel\RCS\Suggestions\Dial;
use Vonage\Messages\Channel\RCS\Suggestions\OpenUrl;
use Vonage\Messages\Channel\RCS\Suggestions\OpenUrlWebView;
use Vonage\Messages\Channel\RCS\Suggestions\Reply;
use Vonage\Messages\Channel\RCS\Suggestions\ShareLocation;
use Vonage\Messages\Channel\RCS\Suggestions\ViewLocation;
use Vonage\Messages\MessageObjects\AudioObject;
use Vonage\Messages\MessageObjects\FileObject;
use Vonage\Messages\MessageObjects\ImageObject;
use Vonage\Messages\MessageObjects\VideoObject;

class MessengerClientTest extends MessagesClientTest
{
    public function testCanSendMessengerText(): void
    {
        $payload = [
            'to' => '10152368852405295',
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


    public function testCanSendRcsCardWithReplySuggestion(): void
    {
        $replySuggestion = new Reply('Yes', 'reply_yes');

        $cardObject = new RcsCardObject(
            'Card Title',
            'This is some text to display on the card',
            'https://example.com/image.jpg',
            'Image description for accessibility purposes',
            RcsCardObject::HEIGHT_TALL,
            'https://example.com/thumbnail.jpg',
            true,
        );
        $cardObject->setSuggestions(new \Vonage\Messages\Channel\RCS\Suggestions\RcsSuggestionCollection([$replySuggestion]));

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'card' => $cardObject
        ];

        $message = new RcsCard($payload['to'], $payload['from'], $payload['card']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload, $cardObject, $replySuggestion) {
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('channel', 'rcs', $request);
            $this->assertRequestJsonBodyContains('message_type', 'card', $request);

            $expectedCard = $cardObject->toArray();
            $this->assertRequestJsonBodyContains('card', $expectedCard, $request);

            return true;
        }))->willReturn($this->getResponse('rcs-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendRcsCardWithDialSuggestion(): void
    {
        $dialSuggestion = new Dial(
            'Call us',
            'dial_postback',
            '+14155551234',
            'https://example.com/fallback'
        );

        $cardObject = new RcsCardObject(
            'Contact Us',
            'Call our support line',
            'https://example.com/phone.jpg',
            'Phone icon',
            RcsCardObject::HEIGHT_MEDIUM,
            'https://example.com/phone-thumb.jpg',
            false,
        );
        $cardObject->setSuggestions(new \Vonage\Messages\Channel\RCS\Suggestions\RcsSuggestionCollection([$dialSuggestion]));

        $message = new RcsCard('447700900000', '16105551212', $cardObject);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($cardObject) {
            $this->assertRequestJsonBodyContains('channel', 'rcs', $request);
            $this->assertRequestJsonBodyContains('message_type', 'card', $request);
            $this->assertRequestJsonBodyContains('card', $cardObject->toArray(), $request);

            return true;
        }))->willReturn($this->getResponse('rcs-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendRcsCardWithOpenUrlSuggestion(): void
    {
        $openUrlSuggestion = new OpenUrl(
            'Visit our website',
            'open_url_postback',
            'https://example.com',
            'Our company website'
        );

        $cardObject = new RcsCardObject(
            'Learn More',
            'Visit our website for more information',
            'https://example.com/website.jpg',
            'Website screenshot',
            RcsCardObject::HEIGHT_SHORT,
            '',
            false,
        );
        $cardObject->setSuggestions(new \Vonage\Messages\Channel\RCS\Suggestions\RcsSuggestionCollection([$openUrlSuggestion]));

        $message = new RcsCard('447700900000', '16105551212', $cardObject);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($cardObject) {
            $this->assertRequestJsonBodyContains('channel', 'rcs', $request);
            $this->assertRequestJsonBodyContains('message_type', 'card', $request);
            $this->assertRequestJsonBodyContains('card', $cardObject->toArray(), $request);

            return true;
        }))->willReturn($this->getResponse('rcs-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendRcsCardWithOpenUrlWebViewSuggestion(): void
    {
        $openUrlWebViewSuggestion = new OpenUrlWebView(
            'Open in app',
            'webview_postback',
            'https://example.com/product',
            'Product details',
            OpenUrlWebView::VIEW_MODE_TALL
        );

        $cardObject = new RcsCardObject(
            'Product Details',
            'View in our app',
            'https://example.com/product.jpg',
            'Product image',
            RcsCardObject::HEIGHT_MEDIUM,
            '',
            false,
        );
        $cardObject->setSuggestions(new \Vonage\Messages\Channel\RCS\Suggestions\RcsSuggestionCollection([$openUrlWebViewSuggestion]));

        $message = new RcsCard('447700900000', '16105551212', $cardObject);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($cardObject) {
            $this->assertRequestJsonBodyContains('channel', 'rcs', $request);
            $this->assertRequestJsonBodyContains('message_type', 'card', $request);
            $this->assertRequestJsonBodyContains('card', $cardObject->toArray(), $request);

            return true;
        }))->willReturn($this->getResponse('rcs-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendRcsCardWithShareLocationSuggestion(): void
    {
        $shareLocationSuggestion = new ShareLocation(
            'Share your location',
            'share_location_postback'
        );

        $cardObject = new RcsCardObject(
            'Find Us',
            'Share your location to get directions',
            'https://example.com/map.jpg',
            'Map image',
            RcsCardObject::HEIGHT_TALL,
            '',
            false,
        );
        $cardObject->setSuggestions(new \Vonage\Messages\Channel\RCS\Suggestions\RcsSuggestionCollection([$shareLocationSuggestion]));

        $message = new RcsCard('447700900000', '16105551212', $cardObject);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($cardObject) {
            $this->assertRequestJsonBodyContains('channel', 'rcs', $request);
            $this->assertRequestJsonBodyContains('message_type', 'card', $request);
            $this->assertRequestJsonBodyContains('card', $cardObject->toArray(), $request);

            return true;
        }))->willReturn($this->getResponse('rcs-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendRcsCardWithViewLocationSuggestion(): void
    {
        $viewLocationSuggestion = new ViewLocation(
            'View on map',
            'view_location_postback',
            '37.7749',
            '-122.4194',
            'Our Office',
            'https://example.com/fallback'
        );

        $cardObject = new RcsCardObject(
            'Our Location',
            'Visit our office',
            'https://example.com/office.jpg',
            'Office building',
            RcsCardObject::HEIGHT_MEDIUM,
            '',
            false,
        );
        $cardObject->setSuggestions(new \Vonage\Messages\Channel\RCS\Suggestions\RcsSuggestionCollection([$viewLocationSuggestion]));

        $message = new RcsCard('447700900000', '16105551212', $cardObject);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($cardObject) {
            $this->assertRequestJsonBodyContains('channel', 'rcs', $request);
            $this->assertRequestJsonBodyContains('message_type', 'card', $request);
            $this->assertRequestJsonBodyContains('card', $cardObject->toArray(), $request);

            return true;
        }))->willReturn($this->getResponse('rcs-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }


    public function testCanSendRcsCardWithMultipleSuggestions(): void
    {
        $replySuggestion = new Reply('Yes', 'reply_yes');
        $dialSuggestion = new Dial('Call us', 'dial_postback', '+14155551234', 'https://example.com/fallback');
        $openUrlSuggestion = new OpenUrl('Visit website', 'url_postback', 'https://example.com', 'Website');

        $cardObject = new RcsCardObject(
            'Multiple Actions',
            'Choose an action',
            'https://example.com/actions.jpg',
            'Action buttons',
            RcsCardObject::HEIGHT_TALL,
            '',
            false,
        );
        $cardObject->setSuggestions(new \Vonage\Messages\Channel\RCS\Suggestions\RcsSuggestionCollection([
            $replySuggestion,
            $dialSuggestion,
            $openUrlSuggestion
        ]));

        $message = new RcsCard('447700900000', '16105551212', $cardObject);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($cardObject) {
            $this->assertRequestJsonBodyContains('channel', 'rcs', $request);
            $this->assertRequestJsonBodyContains('message_type', 'card', $request);
            $cardArray = $cardObject->toArray();
            $this->assertRequestJsonBodyContains('card', $cardArray, $request);
            $this->assertIsArray($cardArray['suggestions']);
            $this->assertCount(3, $cardArray['suggestions']);

            return true;
        }))->willReturn($this->getResponse('rcs-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendRcsCarouselWithSuggestions(): void
    {
        $replySuggestion1 = new Reply('Option 1', 'reply_option_1');
        $replySuggestion2 = new Reply('Option 2', 'reply_option_2');

        $cardObject1 = new RcsCardObject(
            'Card 1',
            'First card with suggestions',
            'https://example.com/card1.jpg',
            'Card 1 image',
            RcsCardObject::HEIGHT_MEDIUM,
            '',
            false,
        );
        $cardObject1->setSuggestions(new \Vonage\Messages\Channel\RCS\Suggestions\RcsSuggestionCollection([$replySuggestion1]));

        $cardObject2 = new RcsCardObject(
            'Card 2',
            'Second card with suggestions',
            'https://example.com/card2.jpg',
            'Card 2 image',
            RcsCardObject::HEIGHT_MEDIUM,
            '',
            false,
        );
        $cardObject2->setSuggestions(new \Vonage\Messages\Channel\RCS\Suggestions\RcsSuggestionCollection([$replySuggestion2]));

        $cards = [$cardObject1, $cardObject2];
        $message = new RcsCarousel('447700900000', '16105551212', $cards);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($cardObject1, $cardObject2) {
            $this->assertRequestJsonBodyContains('channel', 'rcs', $request);
            $this->assertRequestJsonBodyContains('message_type', 'carousel', $request);
            $this->assertRequestJsonBodyContains(
                'carousel',
                [
                    'cards' => [
                        $cardObject1->toArray(),
                        $cardObject2->toArray(),
                    ],
                ],
                $request
            );

            return true;
        }))->willReturn($this->getResponse('rcs-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }
}
