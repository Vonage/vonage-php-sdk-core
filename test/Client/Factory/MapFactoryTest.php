<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Client\Factory;

use Vonage\Client;
use Vonage\Client\Factory\MapFactory;
use PHPUnit\Framework\TestCase;

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

    public function setUp()
    {
        $this->client = new Client(new Client\Credentials\Basic('key', 'secret'));

        $this->factory = new MapFactory([
            'test' => 'VonageTest\Client\Factory\TestDouble'
        ], $this->client);
    }

    public function testClientInjection()
    {
        $api = $this->factory->getApi('test');
        $this->assertSame($this->client, $api->client);
    }

    public function testCache()
    {
        $api = $this->factory->getApi('test');
        $cache = $this->factory->getApi('test');

        $this->assertSame($api, $cache);
    }

    public function testClassMap()
    {
        $this->assertTrue($this->factory->hasApi('test'));
        $this->assertFalse($this->factory->hasApi('not'));

        $api = $this->factory->getApi('test');
        $this->assertInstanceOf('VonageTest\Client\Factory\TestDouble', $api);

        $this->expectException(\RuntimeException::class);
        $this->factory->getApi('not');
    }

    public function testMakeCreatesNewInstance()
    {
        $first = $this->factory->make('test');
        $second = $this->factory->make('test');

        $this->assertNotSame($first, $second);
        $this->assertInstanceOf('VonageTest\Client\Factory\TestDouble', $first);
        $this->assertInstanceOf('VonageTest\Client\Factory\TestDouble', $second);
    }

    public function testMakeDoesNotUseCache()
    {
        $cached = $this->factory->get('test');
        $new = $this->factory->make('test');
        $secondCached = $this->factory->get('test');

        $this->assertNotSame($cached, $new);
        $this->assertNotSame($secondCached, $new);
        $this->assertSame($cached, $secondCached);
    }
}
