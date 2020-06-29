<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Client\Factory;

use Nexmo\Client;
use Nexmo\Client\Factory\MapFactory;
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
            'test' => 'NexmoTest\Client\Factory\TestDouble'
        ], $this->client);
    }

    public function testClientInjection()
    {
        $api = $this->factory->get('test');
        $this->assertSame($this->client, $api->client);
    }

    public function testCache()
    {
        $api = $this->factory->get('test');
        $cache = $this->factory->get('test');

        $this->assertSame($api, $cache);
    }

    public function testClassMap()
    {
        $this->assertTrue($this->factory->has('test'));
        $this->assertFalse($this->factory->has('not'));

        $api = $this->factory->get('test');
        $this->assertInstanceOf('NexmoTest\Client\Factory\TestDouble', $api);

        $this->expectException(\RuntimeException::class);
        $this->factory->get('not');
    }
}
