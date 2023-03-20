<?php

declare(strict_types=1);

namespace VonageTest\Verify2;

use Laminas\Diactoros\Request\Serializer;
use Laminas\Diactoros\ServerRequest;
use VonageTest\VonageTestCase;

class WebhooksTest extends VonageTestCase
{
    public function testCanHydrateWebhook(): void
    {
        $expected = $this->getBodyFromRequest('valid-webhook');
        $request = $this->getServerRequest('valid-webhook');
        $incomingWebhook = \Vonage\Verify2\Webhook\Factory::createFromRequest($request);

        $this->assertSame($expected['request_id'], $incomingWebhook->getRequestId());
        $this->assertSame($expected['triggered_at'], $incomingWebhook->getTriggeredAt());
        $this->assertSame($expected['type'], $incomingWebhook->getType());
        $this->assertSame($expected['channel'], $incomingWebhook->getChannel());
        $this->assertSame($expected['status'], $incomingWebhook->getStatus());
        $this->assertSame($expected['finalized_at'], $incomingWebhook->getFinalizedAt());
        $this->assertSame($expected['client_ref'], $incomingWebhook->getClientRef());
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
