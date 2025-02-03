<?php

declare(strict_types=1);

namespace VonageTest\Client\Credentials\Handler;

use GuzzleHttp\Psr7\Request;
use Vonage\Client\Credentials\Handler\SignatureBodyHandler;
use Vonage\Client\Credentials\SignatureSecret;
use VonageTest\VonageTestCase;

class SignatureBodyHandlerTest extends VonageTestCase
{
    public function testSignatureBodyHandler(): void
    {
        $request = new Request(
            'POST',
            '/test',
            ['Content-Type' => 'application/json'],
            json_encode(['foo' => 'bar'])
        );

        $credentials = new SignatureSecret('secret', 'sha256');

        $handler = new SignatureBodyHandler();
        $authRequest = $handler($request, $credentials);
        $authRequest->getBody()->rewind();
        $newBody = $authRequest->getBody()->getContents();
        $newBodyArray = json_decode($newBody, true);

        $this->assertIsArray($newBodyArray);
        $this->assertArrayHasKey('foo', $newBodyArray);
        $this->assertEquals('bar', $newBodyArray['foo']);
        $this->assertArrayHasKey('sig', $newBodyArray);
    }
}