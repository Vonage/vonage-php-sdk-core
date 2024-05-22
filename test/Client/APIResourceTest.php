<?php

declare(strict_types=1);

namespace VonageTest\Client;

use VonageTest\VonageTestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\BasicHandler;
use Vonage\Client\Credentials\Handler\SignatureBodyHandler;

class APIResourceTest extends VonageTestCase
{
    public function testOverridingBaseUrlUsesClientApiUrl(): void
    {
        /** @var mixed $mockClient */
        $mockClient = $this->prophesize(Client::class);
        $mockClient->getApiUrl()->willReturn('https://test.domain');

        $resource = new APIResource();
        $resource->setClient($mockClient->reveal());

        $this->assertSame('https://test.domain', $resource->getBaseUrl());
    }

    public function testOverridingBaseUrlManuallyWorks(): void
    {
        $resource = new APIResource();
        $resource->setBaseUrl('https://test.domain');

        $this->assertSame('https://test.domain', $resource->getBaseUrl());
    }

    public function testNotOverridingBaseURLReturnsBlank(): void
    {
        $resource = new APIResource();
        $this->assertSame('', $resource->getBaseUrl());
    }

    public function testCanSetMultipleAuthHandlers(): void
    {
        $resource = new APIResource();
        $resource->setAuthHandlers([new BasicHandler(), new SignatureBodyHandler()]);

        $this->assertIsArray($resource->getAuthHandlers());
    }

    public function testSingleAuthHanlderConvertedToArray(): void
    {
        $resource = new APIResource();
        $resource->setAuthHandlers(new BasicHandler());

        $this->assertIsArray($resource->getAuthHandlers());
    }
}
