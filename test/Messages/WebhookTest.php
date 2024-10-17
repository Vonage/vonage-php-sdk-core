<?php

declare(strict_types=1);

namespace VonageTest\Messages;

use Vonage\Messages\Webhook\Factory;
use Vonage\Messages\Webhook\InboundMessenger;
use Vonage\Messages\Webhook\InboundMMS;
use Vonage\Messages\Webhook\InboundRCS;
use Vonage\Messages\Webhook\InboundSMS;
use Vonage\Messages\Webhook\InboundViber;
use Vonage\Messages\Webhook\InboundWhatsApp;
use VonageTest\VonageTestCase;

class WebhookTest extends VonageTestCase
{
    public function testWillHydrateIncomingSMS(): void
    {
        $smsDecoded = [
            "message_type" => "text",
            "text" => "Hello from Vonage!",
            "to" => "447700900000",
            "from" => "447700900001",
            "channel" => "sms",
            "ttl" => 90000,
            "sms" => [
                "encoding_type" => "text",
                "content_id" => "1107457532145798767",
                "entity_id" => "1101456324675322134",
            ],
            "webhook_version" => "v1",
            "client_ref" => "abc123",
            "webhook_url" => "https://example.com/status",
        ];

        $webhook = Factory::createFromArray($smsDecoded);
        $this->assertInstanceOf(InboundSMS::class, $webhook);
    }

    public function testWillHydrateIncomingMMSImage(): void
    {
        $mmsPayload = [
            "message_type" => "image",
            "image" => [
                "url" => "https://example.com/image.jpg",
                "caption" => "Additional text to accompany the image.",
            ],
            "to" => "447700900000",
            "from" => "447700900001",
            "channel" => "mms",
            "ttl" => 600,
            "webhook_version" => "v1",
            "client_ref" => "abc123",
            "webhook_url" => "https://example.com/status",
        ];

        $webhook = Factory::createFromArray($mmsPayload);
        $this->assertInstanceOf(InboundMMS::class, $webhook);
    }

    public function testWillHydrateIncomingRCS(): void
    {
        $rcsPayload = [
            "message_type" => "text",
            "text" => "Hello from Vonage!",
            "to" => "447700900000",
            "from" => "Vonage",
            "channel" => "rcs",
            "ttl" => 600,
            "client_ref" => "abc123",
            "webhook_url" => "https://example.com/status",
        ];

        $webhook = Factory::createFromArray($rcsPayload);
        $this->assertInstanceOf(InboundRCS::class, $webhook);
    }

    public function testWillHydrateIncomingMMSText(): void
    {
        $mmsPayload = [
            "channel" => "mms",
            "message_uuid" => "aaaaaaaa-bbbb-4ccc-8ddd-0123456789ab",
            "to" => "447700900000",
            "from" => "447700900001",
            "timestamp" => "2020-01-01T14:00:00.000Z",
            "origin" => ["network_code" => "12345"],
            "message_type" => "text",
            "text" => "This is sample text.",
        ];

        $webhook = Factory::createFromArray($mmsPayload);
        $this->assertInstanceOf(InboundMMS::class, $webhook);
    }

    public function testWillHydrateIncomingWhatsapp(): void
    {
        $whatsAppPayload = [
            "channel" => "whatsapp",
            "message_uuid" => "aaaaaaaa-bbbb-4ccc-8ddd-0123456789ab",
            "to" => "447700900000",
            "from" => "447700900001",
            "timestamp" => "2020-01-01T14:00:00.000Z",
            "profile" => ["name" => "Jane Smith"],
            "context_status" => "available",
            "context" => [
                "message_uuid" => "aaaaaaaa-bbbb-4ccc-8ddd-0123456789ab",
                "message_from" => "447700900000",
            ],
            "provider_message" => "string",
            "message_type" => "text",
            "text" => "Hello from Vonage!",
            "whatsapp" => [
                "referral" => [
                    "body" => "Check out our new product offering",
                    "headline" => "New Products!",
                    "source_id" => "212731241638144",
                    "source_type" => "post",
                    "source_url" => "https://fb.me/2ZulEu42P",
                    "media_type" => "image",
                    "image_url" => "https://example.com/image.jpg",
                    "video_url" => "https://example.com/video.mp4",
                    "thumbnail_url" => "https://example.com/thumbnail.jpg",
                    "ctwa_clid" => "1234567890",
                ],
            ],
            "_self" => [
                "href" =>
                    "https://api-eu.vonage.com/v1/messages/aaaaaaa-bbbb-4ccc-8ddd-0123456789ab",
            ],
        ];

        $webhook = Factory::createFromArray($whatsAppPayload);
        $this->assertInstanceOf(InboundWhatsApp::class, $webhook);
    }

    public function testWillHydrateIncomingMessenger(): void
    {
        $messengerPayload = [
            "channel" => "messenger",
            "message_uuid" => "aaaaaaaa-bbbb-4ccc-8ddd-0123456789ab",
            "to" => "0123456789",
            "from" => "9876543210",
            "timestamp" => "2020-01-01T14:00:00.000Z",
            "message_type" => "text",
            "text" => "Hello from Vonage!",
        ];

        $webhook = Factory::createFromArray($messengerPayload);
        $this->assertInstanceOf(InboundMessenger::class, $webhook);
    }

    public function testWillHydrateIncomingViberChannel(): void
    {
        $viberPayload = [
            "channel" => "viber_service",
            "context" => ["message_uuid" => "1234567890abcdef"],
            "message_uuid" => "aaaaaaaa-bbbb-4ccc-8ddd-0123456789ab",
            "to" => "0123456789",
            "from" => "447700900001",
            "timestamp" => "2020-01-01T14:00:00.000Z",
            "message_type" => "text",
            "text" => "Hello from Vonage!",
        ];

        $webhook = Factory::createFromArray($viberPayload);
        $this->assertInstanceOf(InboundViber::class, $webhook);
    }

    public function testWillThrowErrorWithoutChannel(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'channel' key is missing in the incoming data.");

        $payload = [
            "context" => ["message_uuid" => "1234567890abcdef"],
            "message_uuid" => "aaaaaaaa-bbbb-4ccc-8ddd-0123456789ab",
            "to" => "0123456789",
            "from" => "447700900001",
            "timestamp" => "2020-01-01T14:00:00.000Z",
            "message_type" => "text",
            "text" => "Hello from Vonage!",
        ];

        $webhook = Factory::createFromArray($payload);
    }

    public function testWillThrowErrorWithoutValidChannel(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Unable to determine incoming webhook type for channel: signal_service");

        $payload = [
            "channel" => "signal_service",
            "context" => ["message_uuid" => "1234567890abcdef"],
            "message_uuid" => "aaaaaaaa-bbbb-4ccc-8ddd-0123456789ab",
            "to" => "0123456789",
            "from" => "447700900001",
            "timestamp" => "2020-01-01T14:00:00.000Z",
            "message_type" => "text",
            "text" => "Hello from Vonage!",
        ];

        $webhook = Factory::createFromArray($payload);
    }
}
