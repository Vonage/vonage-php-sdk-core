<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Client\Credentials;


use Nexmo\Client\Credentials\Container;
use Nexmo\Client\Credentials\Keypair;
use Nexmo\Client\Credentials\Basic;
use Nexmo\Client\Credentials\OAuth;
use Nexmo\Client\Credentials\SharedSecret;
use Webmozart\Expression\Selector\Key;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    protected $types = [
        Basic::class,
        SharedSecret::class,
        Keypair::class
    ];

    protected $basic;
    protected $secret;
    protected $keypair;

    public function setUp()
    {
        $this->basic = new Basic('key', 'secret');
        $this->secret = new SharedSecret('key', 'secret');
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
            [new SharedSecret('key', 'secret'), SharedSecret::class],
            [new Keypair('key', 'app'), Keypair::class]
        ];
    }
}
