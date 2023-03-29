<?php

declare(strict_types=1);

namespace VonageTest\Verify2;

use Laminas\Diactoros\Request\Serializer;
use Laminas\Diactoros\ServerRequest;
use Vonage\Verify2\VerifyObjects\VerifyEvent;
use Vonage\Verify2\VerifyObjects\VerifySilentAuthEvent;
use Vonage\Verify2\VerifyObjects\VerifyStatusUpdate;
use Vonage\Verify2\VerifyObjects\VerifyWhatsAppInteractiveEvent;
use Vonage\Verify2\Webhook\Factory;
use VonageTest\VonageTestCase;

class WebhooksTest extends VonageTestCase
{
    public function testCanHydrateEvent(): void
    {
        $expected = $this->getBodyFromRequest('event-webhook');
        $request = $this->getServerRequest('event-webhook');
        $incomingWebhook = Factory::createFromRequest($request);

        $this->assertInstanceOf(VerifyEvent::class, $incomingWebhook);
        $this->assertSame($expected['request_id'], $incomingWebhook->request_id);
        $this->assertSame($expected['triggered_at'], $incomingWebhook->triggered_at);
        $this->assertSame($expected['type'], $incomingWebhook->type);
        $this->assertSame($expected['channel'], $incomingWebhook->channel);
        $this->assertSame($expected['status'], $incomingWebhook->status);
        $this->assertSame($expected['finalized_at'], $incomingWebhook->finalized_at);
        $this->assertSame($expected['client_ref'], $incomingWebhook->client_ref);
    }

    public function testCanHydrateWhatsAppEvent(): void
    {
        $request = $this->getServerRequest('event-whatsapp-webhook');
        $incomingWebhook = Factory::createFromRequest($request);

        $this->assertInstanceOf(VerifyWhatsAppInteractiveEvent::class, $incomingWebhook);
    }

    public function testCanHydrateStatusUpdate(): void
    {
        $expected = $this->getBodyFromRequest('status-webhook');
        $request = $this->getServerRequest('status-webhook');
        $incomingWebhook = Factory::createFromRequest($request);

        $this->assertInstanceOf(VerifyStatusUpdate::class, $incomingWebhook);
        $this->assertSame($expected['request_id'], $incomingWebhook->request_id);
        $this->assertSame($expected['submitted_at'], $incomingWebhook->submitted_at);
        $this->assertSame($expected['status'], $incomingWebhook->status);
        $this->assertSame($expected['type'], $incomingWebhook->type);
        $this->assertSame($expected['channel_timeout'], $incomingWebhook->channel_timeout);
        $this->assertSame($expected['workflow'], $incomingWebhook->workflow);
    }

    public function testCanHydrateSilentAuthUpdate(): void
    {
        $expected = $this->getBodyFromRequest('silent-auth-webhook');
        $request = $this->getServerRequest('silent-auth-webhook');
        $incomingWebhook = Factory::createFromRequest($request);

        $this->assertInstanceOf(VerifySilentAuthEvent::class, $incomingWebhook);
        $this->assertSame($expected['request_id'], $incomingWebhook->request_id);
        $this->assertSame($expected['triggered_at'], $incomingWebhook->triggered_at);
        $this->assertSame($expected['type'], $incomingWebhook->type);
        $this->assertSame($expected['channel'], $incomingWebhook->channel);
        $this->assertSame($expected['status'], $incomingWebhook->status);
        $this->assertSame($expected['action'], $incomingWebhook->action);
    }

    protected function getQueryStringFromRequest(string $requestName): array
    {
        $text = file_get_contents(__DIR__ . '/Fixtures/Requests/' . $requestName . '.txt');
        $request = Serializer::fromString($text);

        parse_str($request->getUri()->getQuery(), $query);

        return $query;
    }

    protected function getBodyFromRequest(string $requestName, $json = true)
    {
        $text = file_get_contents(__DIR__ . '/Fixtures/Requests/' . $requestName . '.txt');
        $request = Serializer::fromString($text);

        if ($json) {
            return json_decode($request->getBody()->getContents(), true);
        }

        parse_str($request->getBody()->getContents(), $params);

        return $params;
    }

    protected function getServerRequest(string $requestName): ServerRequest
    {
        $text = file_get_contents(__DIR__ . '/Fixtures/Requests/' . $requestName . '.txt');
        $request = Serializer::fromString($text);

        parse_str($request->getUri()->getQuery(), $query);

        return new ServerRequest(
            [],
            [],
            $request->getHeader('Host')[0],
            $request->getMethod(),
            $request->getBody(),
            $request->getHeaders(),
            [],
            $query
        );
    }
}
