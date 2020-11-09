<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Client;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;

class APIResourceTest extends TestCase
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
}
