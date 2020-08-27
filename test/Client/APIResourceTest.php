<?php
declare(strict_types=1);

namespace NexmoTest\Client;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;

class APIResourceTest extends TestCase
{
    public function testOverridingBaseUrlUsesClientApiUrl()
    {
        $mockClient = $this->prophesize(Client::class);
        $mockClient->getApiUrl()->willReturn('https://test.domain');

        $resource = new APIResource();
        $resource->setClient($mockClient->reveal());

        $this->assertSame('https://test.domain', $resource->getBaseUrl());
    }

    public function testOverridingBaseUrlManuallyWorks()
    {
        $resource = new APIResource();
        $resource->setBaseUrl('https://test.domain');

        $this->assertSame('https://test.domain', $resource->getBaseUrl());
    }

    public function testNotOverridingBaseURLReturnsBlank()
    {
        $resource = new APIResource();
        $this->assertSame('', $resource->getBaseUrl());
    }
}
