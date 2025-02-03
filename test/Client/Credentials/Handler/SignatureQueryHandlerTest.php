<?php

declare(strict_types=1);

namespace VonageTest\Client\Credentials\Handler;

use GuzzleHttp\Psr7\Request;
use Vonage\Client\Credentials\Handler\SignatureQueryHandler;
use Vonage\Client\Credentials\SignatureSecret;
use VonageTest\VonageTestCase;
class SignatureQueryHandlerTest extends VonageTestCase
{
    public function testSignatureQueryHandler(): void
    {
        $request = new Request(
            'POST',
            '/test',
            ['Content-Type' => 'application/json'],
            json_encode(['foo' => 'bar'])
        );

        $credentials = new SignatureSecret('secret', 'sha256');

        $handler = new SignatureQueryHandler();
        $authRequest = $handler($request, $credentials);

        $this->assertStringContainsString('sig', $authRequest->getUri()->getQuery());
        $this->assertStringContainsString('timestamp', $authRequest->getUri()->getQuery());
    }
}