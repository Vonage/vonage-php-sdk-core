<?php

declare(strict_types=1);

namespace VonageTest\Verify2;

use Laminas\Diactoros\Request\Serializer;
use Laminas\Diactoros\ServerRequest;
use Vonage\Verify2\VerifyObjects\VerifyEvent;
use Vonage\Verify2\VerifyObjects\VerifySilentAuthUpdate;
use Vonage\Verify2\VerifyObjects\VerifyStatusUpdate;
use VonageTest\VonageTestCase;

class WebhooksTest extends VonageTestCase
{
    public function testCanHydrateStatusUpdate(): void
    {
        $expected = $this->getBodyFromRequest('status-webhook');
        $request = $this->getServerRequest('status-webhook');
        $incomingWebhook = \Vonage\Verify2\Webhook\Factory::createFromRequest($request);

        $this->assertInstanceOf(VerifyStatusUpdate::class, $incomingWebhook);
    }

    public function testCanHydrateEvent(): void
    {
        $expected = $this->getBodyFromRequest('event-webhook');
        $request = $this->getServerRequest('event-webhook');
        $incomingWebhook = \Vonage\Verify2\Webhook\Factory::createFromRequest($request);

        $this->assertInstanceOf(VerifyEvent::class, $incomingWebhook);
        $this->assertSame($expected['request_id'], $incomingWebhook->getRequestId());
        $this->assertSame($expected['triggered_at'], $incomingWebhook->getTriggeredAt());
        $this->assertSame($expected['type'], $incomingWebhook->getType());
        $this->assertSame($expected['channel'], $incomingWebhook->getChannel());
        $this->assertSame($expected['status'], $incomingWebhook->getStatus());
        $this->assertSame($expected['finalized_at'], $incomingWebhook->getFinalizedAt());
        $this->assertSame($expected['client_ref'], $incomingWebhook->getClientRef());
    }

    public function getCanHydrateStatusUpdate(): void
    {
        $expected = $this->getBodyFromRequest('status-webhook');
        $request = $this->getServerRequest('status-webhook');
        $incomingWebhook = \Vonage\Verify2\Webhook\Factory::createFromRequest($request);

        $this->assertInstanceOf(VerifyStatusUpdate::class, $incomingWebhook);
        $this->assertSame($expected['request_id'], $incomingWebhook->getRequestId());
        $this->assertSame($expected['submitted_at'], $incomingWebhook->getSubmittedAt());
        $this->assertSame($expected['status'], $incomingWebhook->getStatus());
        $this->assertSame($expected['type'], $incomingWebhook->getType());
        $this->assertSame($expected['channel_timeout'], $incomingWebhook->getChannelTimeout());
        $this->assertSame($expected['price'], $incomingWebhook->getPrice());
        $this->assertSame($expected['workflow'], $incomingWebhook->getWorkflow());
    }

    public function getCanHydrateSilentAuthUpdate(): void
    {
        $expected = $this->getBodyFromRequest('silent-auth-webhook');
        $request = $this->getServerRequest('silent-auth-webhook');
        $incomingWebhook = \Vonage\Verify2\Webhook\Factory::createFromRequest($request);

        $this->assertInstanceOf(VerifySilentAuthUpdate::class, $incomingWebhook);
        $this->assertSame($expected['request_id'], $incomingWebhook->getRequestId());
        $this->assertSame($expected['triggered_at'], $incomingWebhook->getTriggeredAt());
        $this->assertSame($expected['type'], $incomingWebhook->getType());
        $this->assertSame($expected['channel'], $incomingWebhook->getChannel());
        $this->assertSame($expected['status'], $incomingWebhook->getStatus());
        $this->assertSame($expected['action'], $incomingWebhook->getAction());
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
