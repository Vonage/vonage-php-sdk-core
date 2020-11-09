<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Client\Factory;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Vonage\Client;
use Vonage\Client\Factory\MapFactory;

class MapFactoryTest extends TestCase
{
    /**
     * @var MapFactory
     */
    protected $factory;

    /**
     * @var Client
     */
    protected $client;

    public function setUp(): void
    {
        $this->client = new Client(new Client\Credentials\Basic('key', 'secret'));

        $this->factory = new MapFactory([
            'test' => TestDouble::class
        ], $this->client);
    }

    public function testClientInjection(): void
    {
        $api = $this->factory->getApi('test');
        $this->assertSame($this->client, $api->client);
    }

    public function testCache(): void
    {
        $api = $this->factory->getApi('test');
        $cache = $this->factory->getApi('test');

        $this->assertSame($api, $cache);
    }

    public function testClassMap(): void
    {
        $this->assertTrue($this->factory->hasApi('test'));
        $this->assertFalse($this->factory->hasApi('not'));

        $api = $this->factory->getApi('test');
        $this->assertInstanceOf(TestDouble::class, $api);

        $this->expectException(RuntimeException::class);
        $this->factory->getApi('not');
    }

    public function testMakeCreatesNewInstance(): void
    {
        $first = $this->factory->make('test');
        $second = $this->factory->make('test');

        $this->assertNotSame($first, $second);
        $this->assertInstanceOf(TestDouble::class, $first);
        $this->assertInstanceOf(TestDouble::class, $second);
    }

    public function testMakeDoesNotUseCache(): void
    {
        $cached = $this->factory->get('test');
        $new = $this->factory->make('test');
        $secondCached = $this->factory->get('test');

        $this->assertNotSame($cached, $new);
        $this->assertNotSame($secondCached, $new);
        $this->assertSame($cached, $secondCached);
    }
}
