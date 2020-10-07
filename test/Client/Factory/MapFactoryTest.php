<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test\Client\Factory;

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
        self::assertSame($this->client, $api->client);
    }

    public function testCache(): void
    {
        $api = $this->factory->getApi('test');
        $cache = $this->factory->getApi('test');

        self::assertSame($api, $cache);
    }

    public function testClassMap(): void
    {
        self::assertTrue($this->factory->hasApi('test'));
        self::assertFalse($this->factory->hasApi('not'));

        $api = $this->factory->getApi('test');
        self::assertInstanceOf(TestDouble::class, $api);

        $this->expectException(RuntimeException::class);
        $this->factory->getApi('not');
    }

    public function testMakeCreatesNewInstance(): void
    {
        $first = $this->factory->make('test');
        $second = $this->factory->make('test');

        self::assertNotSame($first, $second);
        self::assertInstanceOf(TestDouble::class, $first);
        self::assertInstanceOf(TestDouble::class, $second);
    }

    public function testMakeDoesNotUseCache(): void
    {
        $cached = $this->factory->get('test');
        $new = $this->factory->make('test');
        $secondCached = $this->factory->get('test');

        self::assertNotSame($cached, $new);
        self::assertNotSame($secondCached, $new);
        self::assertSame($cached, $secondCached);
    }
}
