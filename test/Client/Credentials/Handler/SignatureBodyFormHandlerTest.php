<?php

declare(strict_types=1);

namespace VonageTest\Client\Credentials\Handler;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Vonage\Client\Credentials\SignatureSecret;
use VonageTest\VonageTestCase;
use Vonage\Client\Credentials\Handler\SignatureBodyFormHandler;

class SignatureBodyFormHandlerTest extends VonageTestCase
{
    public function testSignatureBodyFormHandler()
    {
        $initialBody = http_build_query(['param1' => 'value1', 'param2' => 'value2']);

        $request = new Request(
            'POST',
            '/test',
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            Utils::streamFor($initialBody)
        );

        $credentials = new SignatureSecret('secret', 'sha256');

        $handler = new SignatureBodyFormHandler();
        $authRequest = $handler($request, $credentials);
        $authRequest->getBody()->rewind();
        $newBody = $authRequest->getBody()->getContents();

        parse_str($newBody, $params);

        $this->assertArrayHasKey('api_key', $params);
        $this->assertArrayHasKey('sig', $params);
    }
}