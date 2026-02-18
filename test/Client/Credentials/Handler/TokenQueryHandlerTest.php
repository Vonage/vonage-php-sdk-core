<?php

declare(strict_types=1);

namespace VonageTest\Client\Credentials\Handler;

use GuzzleHttp\Psr7\Request;
use Vonage\Client\Credentials\Basic;
use Vonage\Client\Credentials\Handler\TokenQueryHandler;
use VonageTest\VonageTestCase;

class TokenQueryHandlerTest extends VonageTestCase
{
    public function testTokenBodyHandler(): void
    {
        $request = new Request(
            'POST',
            '/test',
            ['Content-Type' => 'application/json'],
            json_encode(['foo' => 'bar'])
        );

        $credentials = new Basic('secret', 'sha256');

        $handler = new TokenQueryHandler();
        $authRequest = $handler($request, $credentials);

        $basicAuth = base64_encode('secret:sha256');

        $this->assertStringContainsString($basicAuth, $authRequest->getHeader('Authorization')[0]);
    }
}

