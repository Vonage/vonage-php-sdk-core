<?php

namespace VonageTest\Messages;

use Laminas\Diactoros\Request;
use Prophecy\Argument;
use Vonage\Messages\Channel\RCS\RcsBase;
use Vonage\Messages\Channel\RCS\RcsCard;
use Vonage\Messages\Channel\RCS\RCSCardAlignment;
use Vonage\Messages\Channel\RCS\RcsCardObject;
use Vonage\Messages\Channel\RCS\RCSCardOrentation;
use Vonage\Messages\Channel\RCS\RcsCarousel;
use Vonage\Messages\Channel\RCS\RCSCategory;
use Vonage\Messages\Channel\RCS\RcsCustom;
use Vonage\Messages\Channel\RCS\RcsFile;
use Vonage\Messages\Channel\RCS\RcsImage;
use Vonage\Messages\Channel\RCS\RcsInvalidTtlException;
use Vonage\Messages\Channel\RCS\RcsText;
use Vonage\Messages\Channel\RCS\RcsVideo;
use Vonage\Messages\Channel\RCS\Suggestions\CreateCalendarEvent;
use Vonage\Messages\Channel\RCS\Suggestions\Dial;
use Vonage\Messages\Channel\RCS\Suggestions\OpenUrl;
use Vonage\Messages\Channel\RCS\Suggestions\OpenUrlWebView;
use Vonage\Messages\Channel\RCS\Suggestions\RcsSuggestionCollection;
use Vonage\Messages\Channel\RCS\Suggestions\Reply;
use Vonage\Messages\Channel\RCS\Suggestions\ShareLocation;
use Vonage\Messages\Channel\RCS\Suggestions\Suggestion;
use Vonage\Messages\Channel\RCS\Suggestions\ViewLocation;
use Vonage\Messages\Client as MessagesClient;
use Vonage\Messages\MessageObjects\FileObject;
use Vonage\Messages\MessageObjects\ImageObject;
use Vonage\Messages\MessageObjects\VideoObject;

class RCSClientTest extends MessagesClientTest
{

    public static function suggestionProvider(): array
    {

        $dialSuggestion = new Dial(
            'Call us',
            'dial_postback',
            '+14155551234',
            'https://example.com/fallback'
        );

        $openUrlSuggestion = new OpenUrl(
            'Visit our website',
            'open_url_postback',
            'https://example.com',
            'Our company website'
        );

        $openUrlWebViewSuggestion = new OpenUrlWebView(
            'Open in app',
            'webview_postback',
            'https://example.com/product',
            'Product details',
            OpenUrlWebView::VIEW_MODE_TALL
        );

        $shareLocationSuggestion = new ShareLocation(
            'Share your location',
            'share_location_postback'
        );

        $replySuggestion = new Reply(
            'Yes',
            'reply_yes',
        );

        $viewLocationSuggestion = new ViewLocation(
            'View on map',
            'view_location_postback',
            '37.7749',
            '-122.4194',
            'Our Office',
            'https://example.com/fallback'
        );

        $calendarSuggestion = new CreateCalendarEvent(
            'Option 1',
            'New Year Party',
            'action_1',
            '2023-01-01T10:00:00Z',
            '2023-01-01T11:00:00Z',
            'Join us to celebrate the new year',
            'http://example.com/calendar',
        );

        return [
            'with calendar suggestion' => [
                [$calendarSuggestion],
                [
                    [
                        'text' => 'Option 1',
                        'type' => Suggestion::SUGGESTION_TYPE_CREATE_CALENDAR_EVENT,
                        'postback_data' => 'action_1',
                        'title' => 'New Year Party',
                        'start_time' => '2023-01-01T10:00:00Z',
                        'end_time' => '2023-01-01T11:00:00Z',
                        'description' => 'Join us to celebrate the new year',
                        'fallback_url' => 'http://example.com/calendar',
                    ],
                ],
            ],
            'with dial suggestion' => [
                [$dialSuggestion],
                [
                    [
                        'text' => 'Call us',
                        'type' => Suggestion::SUGGESTION_TYPE_DIAL,
                        'postback_data' => 'dial_postback',
                        'phone_number' => '+14155551234',
                        'fallback_url' => 'https://example.com/fallback',
                    ],
                ]
            ],
            'with open url suggestion' => [
                [$openUrlSuggestion],
                [
                    [
                        'text' => 'Visit our website',
                        'type' => Suggestion::SUGGESTION_TYPE_OPEN_URL,
                        'postback_data' => 'open_url_postback',
                        'url' => 'https://example.com',
                        'description' => 'Our company website'
                    ]
                ]
            ],
            'with open url webview suggestion' => [
                [$openUrlWebViewSuggestion],
                [
                    [
                        'text' => 'Open in app',
                        'type' => Suggestion::SUGGESTION_TYPE_OPEN_URL_WEBVIEW,
                        'postback_data' => 'webview_postback',
                        'url' => 'https://example.com/product',
                        'description' => 'Product details',
                        'view_mode' => OpenUrlWebView::VIEW_MODE_TALL
                    ]
                ]
            ],
            'with share location suggestion' => [
                [$shareLocationSuggestion],
                [
                    [

                        'text' => 'Share your location',
                        'type' => Suggestion::SUGGESTION_TYPE_SHARE_LOCATION,
                        'postback_data' => 'share_location_postback'
                    ],
                ]
            ],
            'with reply suggestion' => [
                [$replySuggestion],
                [
                    [

                        'text' => 'Yes',
                        'type' => Suggestion::SUGGESTION_TYPE_REPLY,
                        'postback_data' => 'reply_yes',
                    ]
                ]
            ],
            'with view location suggestion' => [
                [$viewLocationSuggestion],
                [
                    [
                        'text' => 'View on map',
                        'type' => Suggestion::SUGGESTION_TYPE_VIEW_LOCATION,
                        'postback_data' => 'view_location_postback',
                        'latitude' => '37.7749',
                        'longitude' => '-122.4194',
                        'pin_label' => 'Our Office',
                        'fallback_url' => 'https://example.com/fallback'
                    ]
                ]
            ],
            // In production, there are limits for the number of suggestions
            // Since that limit depends on the type of RCS message, the SDK
            // does not validate the limit and instead lets the API handle
            // validation.
            //
            // Here we want to test that setting the suggestions are transformed
            // properly for the API
            'with multiple suggestions' => [
                [
                    $calendarSuggestion,
                    $dialSuggestion,
                    $openUrlSuggestion,
                    $openUrlWebViewSuggestion,
                    $shareLocationSuggestion,
                    $replySuggestion,
                    $viewLocationSuggestion
                ],
                [
                    [
                        'text' => 'Option 1',
                        'type' => Suggestion::SUGGESTION_TYPE_CREATE_CALENDAR_EVENT,
                        'postback_data' => 'action_1',
                        'title' => 'New Year Party',
                        'start_time' => '2023-01-01T10:00:00Z',
                        'end_time' => '2023-01-01T11:00:00Z',
                        'description' => 'Join us to celebrate the new year',
                        'fallback_url' => 'http://example.com/calendar',
                    ],
                    [
                        'text' => 'Call us',
                        'type' => Suggestion::SUGGESTION_TYPE_DIAL,
                        'postback_data' => 'dial_postback',
                        'phone_number' => '+14155551234',
                        'fallback_url' => 'https://example.com/fallback',
                    ],
                    [
                        'text' => 'Visit our website',
                        'type' => Suggestion::SUGGESTION_TYPE_OPEN_URL,
                        'postback_data' => 'open_url_postback',
                        'url' => 'https://example.com',
                        'description' => 'Our company website'
                    ],
                    [
                        'text' => 'Open in app',
                        'type' => Suggestion::SUGGESTION_TYPE_OPEN_URL_WEBVIEW,
                        'postback_data' => 'webview_postback',
                        'url' => 'https://example.com/product',
                        'description' => 'Product details',
                        'view_mode' => OpenUrlWebView::VIEW_MODE_TALL
                    ],
                    [

                        'text' => 'Share your location',
                        'type' => Suggestion::SUGGESTION_TYPE_SHARE_LOCATION,
                        'postback_data' => 'share_location_postback'
                    ],
                    [

                        'text' => 'Yes',
                        'type' => Suggestion::SUGGESTION_TYPE_REPLY,
                        'postback_data' => 'reply_yes',
                    ],
                    [
                        'text' => 'View on map',
                        'type' => Suggestion::SUGGESTION_TYPE_VIEW_LOCATION,
                        'postback_data' => 'view_location_postback',
                        'latitude' => '37.7749',
                        'longitude' => '-122.4194',
                        'pin_label' => 'Our Office',
                        'fallback_url' => 'https://example.com/fallback'
                    ]
                ]
            ],
        ];
    }

    /**
     * @dataProvider suggestionProvider
     */
    public function testCanSendRcsTextWithSuggestion(array $suggestions, array $expected): void
    {
        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => 'Reticulating Splines',
        ];

        $message = new RcsText($payload['to'], $payload['from'], $payload['text']);
        $this->assertInstanceOf(RcsBase::class, $message);
        $message->setSuggestions(new RcsSuggestionCollection($suggestions));

        $this->vonageClient->send(Argument::that(function (Request $request) use ($expected, $payload) {
            $this->assertEquals(
                'Bearer ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 7)
            );
            $this->assertRequestJsonBodyContains('suggestions', $expected, $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('rcs-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendRcsText(): void
    {
        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => 'Reticulating Splines',
        ];

        $message = new RcsText($payload['to'], $payload['from'], $payload['text']);
        $this->assertInstanceOf(RcsBase::class, $message);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertEquals(
                'Bearer ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 7)
            );
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('text', $payload['text'], $request);
            $this->assertRequestJsonBodyContains('channel', 'rcs', $request);
            $this->assertRequestJsonBodyContains('message_type', 'text', $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('rcs-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendRcsMessageWithAllOptions(): void
    {
        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => 'Reticulating Splines',
            'client_ref' => 'RCS Message',
            'ttl' => 330,
            'category' => RCSCategory::Acknowledgement->value,
            'trusted_recipient' => true,
            'webhook_url' => 'https://example.com/incoming'
        ];

        $message = new RcsText($payload['to'], $payload['from'], $payload['text']);
        $this->assertInstanceOf(RcsBase::class, $message);
        $message->setClientRef($payload['client_ref']);
        $message->setTtl($payload['ttl']);
        $message->setTrustedRecipient($payload['trusted_recipient']);
        $message->setWebhookUrl($payload['webhook_url']);
        $message->setCategory(RCSCategory::Acknowledgement);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertEquals(
                'Bearer ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 7)
            );
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('text', $payload['text'], $request);
            $this->assertRequestJsonBodyContains('channel', 'rcs', $request);
            $this->assertRequestJsonBodyContains('message_type', 'text', $request);

            $this->assertRequestJsonBodyContains('client_ref', $payload['client_ref'], $request);
            $this->assertRequestJsonBodyContains('webhook_url', $payload['webhook_url'], $request);
            $this->assertRequestJsonBodyContains('ttl', $payload['ttl'], $request);
            $this->assertRequestJsonBodyContains('trusted_recipient', $payload['trusted_recipient'], $request);
            $this->assertRequestJsonBodyContains('rcs', ['category' => $payload['category']], $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('rcs-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendRcsImage()
    {
        $image = new ImageObject('https://my-image.com');

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'image' => $image
        ];

        $message = new RcsImage($payload['to'], $payload['from'], $payload['image']);
        $this->assertInstanceOf(RcsBase::class, $message);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertEquals(
                'Bearer ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 7)
            );
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('channel', 'rcs', $request);
            $this->assertRequestJsonBodyContains('message_type', 'image', $request);
            $this->assertRequestJsonBodyContains('image', ['url' => 'https://my-image.com'], $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('rcs-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendRcsVideo(): void
    {
        $videoObject = new VideoObject('https://my-image.com');

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'video' => $videoObject
        ];

        $message = new RcsVideo($payload['to'], $payload['from'], $payload['video']);
        $this->assertInstanceOf(RcsBase::class, $message);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertEquals(
                'Bearer ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 7)
            );
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('message_type', 'video', $request);
            $this->assertRequestJsonBodyContains('video', ['url' => 'https://my-image.com'], $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('rcs-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }


    public function testCanSendRcsFile(): void
    {
        $fileObject = new FileObject('https://example.com/file.pdf');

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'file' => $fileObject
        ];

        $message = new RcsFile($payload['to'], $payload['from'], $payload['file']);
        $this->assertInstanceOf(RcsBase::class, $message);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertEquals(
                'Bearer ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 7)
            );
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('message_type', 'file', $request);
            $this->assertRequestJsonBodyContains('file', ['url' => 'https://example.com/file.pdf'], $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('rcs-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendRcsCard(): void
    {
        $cardObject = new RcsCardObject(
            'Card Title',
            'This is some text to display on the card',
            'https://example.com/image.jpg',
            'Image description for accessibility purposes',
            RcsCardObject::HEIGHT_TALL,
            'https://example.com/thumbnail.jpg',
            true,
        );

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => 'Reticulating Splines',
            'card' => $cardObject
        ];

        $message = new RcsCard($payload['to'], $payload['from'], $payload['card']);
        $this->assertInstanceOf(RcsBase::class, $message);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload, $cardObject) {
            $this->assertEquals(
                'Bearer ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 7)
            );
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('channel', 'rcs', $request);
            $this->assertRequestJsonBodyContains('message_type', 'card', $request);
            $this->assertRequestJsonBodyContains(
                'card',
                [
                    'title' => $cardObject->getTitle(),
                    'text' => $cardObject->getText(),
                    'media_url' => $cardObject->getMediaUrl(),
                    'media_description' => $cardObject->getMediaDescription(),
                    'media_height' => $cardObject->getMediaHeight(),
                    'thumbnail_url' => $cardObject->getThumbnailUrl(),
                    'media_force_refresh' => $cardObject->getMediaForceRefresh(),
                ],
                $request,
            );
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('rcs-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendRcsCardWtihCardOptions(): void
    {
        $cardObject = new RcsCardObject(
            'Card Title',
            'This is some text to display on the card',
            'https://example.com/image.jpg',
            'Image description for accessibility purposes',
            RcsCardObject::HEIGHT_TALL,
            'https://example.com/thumbnail.jpg',
            true,
        );

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => 'Reticulating Splines',
            'card' => $cardObject
        ];

        $message = new RcsCard($payload['to'], $payload['from'], $payload['card']);
        $this->assertInstanceOf(RcsBase::class, $message);
        $message->setOrientation(RCSCardOrentation::Horizontal);
        $message->setImageAlignment(RCSCardAlignment::Left);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload, $cardObject) {
            $this->assertEquals(
                'Bearer ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 7)
            );
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('channel', 'rcs', $request);
            $this->assertRequestJsonBodyContains('message_type', 'card', $request);
            $this->assertRequestJsonBodyContains(
                'card',
                [
                    'title' => $cardObject->getTitle(),
                    'text' => $cardObject->getText(),
                    'media_url' => $cardObject->getMediaUrl(),
                    'media_description' => $cardObject->getMediaDescription(),
                    'media_height' => $cardObject->getMediaHeight(),
                    'thumbnail_url' => $cardObject->getThumbnailUrl(),
                    'media_force_refresh' => $cardObject->getMediaForceRefresh(),
                ],
                $request,
            );

            $this->assertRequestJsonBodyContains(
                'rcs',
                [
                    'image_alignment' => RCSCardAlignment::Left->value,
                    'card_orientation' => RCSCardOrentation::Horizontal->value,
                ],
                $request,
            );
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('rcs-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    /**
     * @dataProvider suggestionProvider
     */
    public function testCanSendRcsCardWithSuggestions(array $suggestions, array $expected): void
    {
        $cardObject = new RcsCardObject(
            'Card Title',
            'This is some text to display on the card',
            'https://example.com/image.jpg',
            'Image description for accessibility purposes',
            RcsCardObject::HEIGHT_TALL,
            'https://example.com/thumbnail.jpg',
            true,
        );

        $cardObject->setSuggestions(new RcsSuggestionCollection($suggestions));

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => 'Reticulating Splines',
            'card' => $cardObject
        ];

        $message = new RcsCard($payload['to'], $payload['from'], $payload['card']);
        $this->assertInstanceOf(RcsBase::class, $message);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($expected, $payload, $cardObject) {
            $this->assertEquals(
                'Bearer ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 7)
            );
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('channel', 'rcs', $request);
            $this->assertRequestJsonBodyContains('message_type', 'card', $request);
            $this->assertRequestJsonBodyContains(
                'card',
                [
                    'title' => $cardObject->getTitle(),
                    'text' => $cardObject->getText(),
                    'media_url' => $cardObject->getMediaUrl(),
                    'media_description' => $cardObject->getMediaDescription(),
                    'media_height' => $cardObject->getMediaHeight(),
                    'thumbnail_url' => $cardObject->getThumbnailUrl(),
                    'media_force_refresh' => $cardObject->getMediaForceRefresh(),
                    'suggestions' => $expected,
                ],
                $request,
            );
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('rcs-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendRcsCaraousel(): void
    {
        $cardObject = new RcsCardObject(
            'Card Title',
            'This is some text to display on the card',
            'https://example.com/image.jpg',
            'Image description for accessibility purposes',
            RcsCardObject::HEIGHT_TALL,
            'https://example.com/thumbnail.jpg',
            true,
        );

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => 'Reticulating Splines',
            'cards' => [
                $cardObject,
                $cardObject,
            ],
        ];

        $message = new RcsCarousel($payload['to'], $payload['from'], $payload['cards']);
        $this->assertInstanceOf(RcsBase::class, $message);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload, $cardObject) {
            $this->assertEquals(
                'Bearer ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 7)
            );
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('channel', 'rcs', $request);
            $this->assertRequestJsonBodyContains('message_type', 'carousel', $request);
            $this->assertRequestJsonBodyContains(
                'carousel',
                [
                    'cards' =>
                    [
                        [
                            'title' => $cardObject->getTitle(),
                            'text' => $cardObject->getText(),
                            'media_url' => $cardObject->getMediaUrl(),
                            'media_description' => $cardObject->getMediaDescription(),
                            'media_height' => $cardObject->getMediaHeight(),
                            'thumbnail_url' => $cardObject->getThumbnailUrl(),
                            'media_force_refresh' => $cardObject->getMediaForceRefresh(),
                        ],
                        [
                            'title' => $cardObject->getTitle(),
                            'text' => $cardObject->getText(),
                            'media_url' => $cardObject->getMediaUrl(),
                            'media_description' => $cardObject->getMediaDescription(),
                            'media_height' => $cardObject->getMediaHeight(),
                            'thumbnail_url' => $cardObject->getThumbnailUrl(),
                            'media_force_refresh' => $cardObject->getMediaForceRefresh(),
                        ],
                    ],
                ],
                $request,
            );
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('rcs-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    /**
     * @dataProvider suggestionProvider
     */
    public function testCanSendRcsCaraouselWithSuggestions(array $suggestions, array $expected): void
    {
        $cardObject = new RcsCardObject(
            'Card Title',
            'This is some text to display on the card',
            'https://example.com/image.jpg',
            'Image description for accessibility purposes',
            RcsCardObject::HEIGHT_TALL,
            'https://example.com/thumbnail.jpg',
            true,
        );
        $cardObject->setSuggestions(new RcsSuggestionCollection($suggestions));

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => 'Reticulating Splines',
            'cards' => [
                $cardObject,
                $cardObject,
            ],
        ];

        $message = new RcsCarousel($payload['to'], $payload['from'], $payload['cards']);
        $this->assertInstanceOf(RcsBase::class, $message);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($expected, $payload, $cardObject) {
            $this->assertEquals(
                'Bearer ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 7)
            );
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('channel', 'rcs', $request);
            $this->assertRequestJsonBodyContains('message_type', 'carousel', $request);
            $this->assertRequestJsonBodyContains(
                'carousel',
                [
                    'cards' =>
                    [
                        [
                            'title' => $cardObject->getTitle(),
                            'text' => $cardObject->getText(),
                            'media_url' => $cardObject->getMediaUrl(),
                            'media_description' => $cardObject->getMediaDescription(),
                            'media_height' => $cardObject->getMediaHeight(),
                            'thumbnail_url' => $cardObject->getThumbnailUrl(),
                            'media_force_refresh' => $cardObject->getMediaForceRefresh(),
                            'suggestions' => $expected,
                        ],
                        [
                            'title' => $cardObject->getTitle(),
                            'text' => $cardObject->getText(),
                            'media_url' => $cardObject->getMediaUrl(),
                            'media_description' => $cardObject->getMediaDescription(),
                            'media_height' => $cardObject->getMediaHeight(),
                            'thumbnail_url' => $cardObject->getThumbnailUrl(),
                            'media_force_refresh' => $cardObject->getMediaForceRefresh(),
                            'suggestions' => $expected,
                        ],
                    ],
                ],
                $request,
            );
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('rcs-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendRcsCustom(): void
    {
        $customObject = [
            'custom_key' => 'custom_value',
        ];

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => 'Reticulating Splines',
            'custom' => $customObject
        ];

        $message = new RcsCustom($payload['to'], $payload['from'], $payload['custom']);
        $this->assertInstanceOf(RcsBase::class, $message);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertEquals(
                'Bearer ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 7)
            );
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('channel', 'rcs', $request);
            $this->assertRequestJsonBodyContains('message_type', 'custom', $request);
            $this->assertRequestJsonBodyContains('custom', ['custom_key' => 'custom_value'], $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('rcs-success', 202));

        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanUpdateRcsMessage(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertEquals(
                'Bearer ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 7)
            );
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals('https://api.nexmo.com/v1/messages/6ce72c29-e454-442a-94f2-47a1cadba45f', $uriString);

            $this->assertRequestJsonBodyContains('status', 'revoked', $request);
            $this->assertEquals('PATCH', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('rcs-update-success'));

        $this->messageClient->updateRcsStatus('6ce72c29-e454-442a-94f2-47a1cadba45f', MessagesClient::RCS_STATUS_REVOKED);
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
    public function testCannotSendRcsTtlOutOfRange()
    {
        $this->expectException(RcsInvalidTtlException::class);

        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => 'Reticulating Splines',
            'ttl' => 100,
        ];

        $message = new RcsText($payload['to'], $payload['from'], $payload['text']);
        $message->setTtl($payload['ttl']);
    }
}
