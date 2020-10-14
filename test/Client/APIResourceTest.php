<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\Test\Client;

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

        self::assertSame('https://test.domain', $resource->getBaseUrl());
    }

    public function testOverridingBaseUrlManuallyWorks(): void
    {
        $resource = new APIResource();
        $resource->setBaseUrl('https://test.domain');

        self::assertSame('https://test.domain', $resource->getBaseUrl());
    }

    public function testNotOverridingBaseURLReturnsBlank(): void
    {
        $resource = new APIResource();
        self::assertSame('', $resource->getBaseUrl());
    }
}
