<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Client\Credentials;

use Vonage\Client\Credentials\Container;
use Vonage\Client\Credentials\Keypair;
use Vonage\Client\Credentials\Basic;
use Vonage\Client\Credentials\OAuth;
use Vonage\Client\Credentials\SignatureSecret;
use Webmozart\Expression\Selector\Key;
use PHPUnit\Framework\TestCase;

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
     */
    public function testBasic($credential, $type)
    {
        $container = new Container($credential);

        $this->assertSame($credential, $container->get($type));
        $this->assertSame($credential, $container[$type]);

        foreach($this->types as $class){
            if($type == $class){
                $this->assertTrue($container->has($class));
            } else {
                $this->assertFalse($container->has($class));
            }
        }
    }

    /**
     * @dataProvider credentials
     */
    public function testOnlyOneType($credential, $type)
    {
        $other = clone $credential;

        $this->expectException('RuntimeException');

        $container = new Container($credential, $other);
    }

    public function testMultiple()
    {
        $container = new Container($this->basic, $this->secret, $this->keypair);

        foreach($this->types as $class){
            $this->assertTrue($container->has($class));
        }

    }

    public function credentials()
    {
        return [
            [new Basic('key', 'secret'), Basic::class],
            [new SignatureSecret('key', 'secret'), SignatureSecret::class],
            [new Keypair('key', 'app'), Keypair::class]
        ];
    }
}
