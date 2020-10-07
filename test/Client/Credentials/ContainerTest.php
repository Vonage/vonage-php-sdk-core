<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test\Client\Credentials;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Credentials\Basic;
use Vonage\Client\Credentials\Container;
use Vonage\Client\Credentials\Keypair;
use Vonage\Client\Credentials\SignatureSecret;

class ContainerTest extends TestCase
{
    protected $types = [
        Basic::class,
        SignatureSecret::class,
        Keypair::class
    ];

    protected $basic;
    protected $secret;
    protected $keypair;

    public function setUp(): void
    {
        $this->basic = new Basic('key', 'secret');
        $this->secret = new SignatureSecret('key', 'secret');
        $this->keypair = new Keypair('key', 'app');
    }

    /**
     * @dataProvider credentials
     * @param $credential
     * @param $type
     */
    public function testBasic($credential, $type): void
    {
        $container = new Container($credential);

        self::assertSame($credential, $container->get($type));
        self::assertSame($credential, $container[$type]);

        foreach ($this->types as $class) {
            if ($type === $class) {
                self::assertTrue($container->has($class));
            } else {
                self::assertFalse($container->has($class));
            }
        }
    }

    /**
     * @dataProvider credentials
     * @param $credential
     */
    public function testOnlyOneType($credential): void
    {
        $this->expectException('RuntimeException');

        new Container($credential, clone $credential);
    }

    public function testMultiple(): void
    {
        $container = new Container($this->basic, $this->secret, $this->keypair);

        foreach ($this->types as $class) {
            self::assertTrue($container->has($class));
        }
    }

    /**
     * @return array[]
     */
    public function credentials(): array
    {
        return [
            [new Basic('key', 'secret'), Basic::class],
            [new SignatureSecret('key', 'secret'), SignatureSecret::class],
            [new Keypair('key', 'app'), Keypair::class]
        ];
    }
}
