<?php

namespace VonageTest\Client\Credentials\Handler;

use Laminas\Diactoros\Request;
use Vonage\Client\Credentials\Basic;
use Vonage\Client\Credentials\Handler\BasicQueryHandler;
use PHPUnit\Framework\TestCase;

class BasicQueryHandlerTest extends TestCase
{
    public function testWillAddCredentialsToRequest(): void
    {
        $request = new Request('https://example.com');
        $credentials = new Basic('abc', 'xyz');
        $handler = new BasicQueryHandler();
        $request = $handler($request, $credentials);

        $uri = $request->getUri();
        $uriString = $uri->__toString();
        $this->assertEquals('https://example.com?api_key=abc&api_secret=xyz', $uriString);
    }
}
