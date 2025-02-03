<?php

declare(strict_types=1);

namespace VonageTest\Client\Credentials\Handler;

use GuzzleHttp\Psr7\Request;
use Vonage\Client\Credentials\Basic;
use Vonage\Client\Credentials\Handler\TokenBodyHandler;
use VonageTest\VonageTestCase;

class TokenBodyHandlerTest extends VonageTestCase
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

        $handler = new TokenBodyHandler();
        $authRequest = $handler($request, $credentials);
        $authRequest->getBody()->rewind();
        $newBody = $authRequest->getBody()->getContents();
        $newBodyArray = json_decode($newBody, true);

        $this->assertIsArray($newBodyArray);
        $this->assertArrayHasKey('foo', $newBodyArray);
        $this->assertArrayHasKey('api_key', $newBodyArray);
        $this->assertArrayHasKey('api_secret', $newBodyArray);
        $this->assertEquals('bar', $newBodyArray['foo']);
    }
}