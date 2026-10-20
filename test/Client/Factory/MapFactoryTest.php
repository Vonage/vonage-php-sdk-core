<?php

declare(strict_types=1);

namespace VonageTest\Client\Factory;

use VonageTest\VonageTestCase;
use RuntimeException;
use Vonage\Client;
use Vonage\Client\Factory\MapFactory;

class MapFactoryTest extends VonageTestCase
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
            'test' => fn($container) => new TestDouble($container->getClient())
        ], $this->client);
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
