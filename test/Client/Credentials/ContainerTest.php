<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Client\Credentials;

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
     *
     * @param $credential
     * @param $type
     */
    public function testBasic($credential, $type): void
    {
        $container = new Container($credential);

        $this->assertSame($credential, $container->get($type));
        $this->assertSame($credential, $container[$type]);

        foreach ($this->types as $class) {
            if ($type === $class) {
                $this->assertTrue($container->has($class));
            } else {
                $this->assertFalse($container->has($class));
            }
        }
    }

    /**
     * @dataProvider credentials
     *
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
            $this->assertTrue($container->has($class));
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
