<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Client\Credentials;

use \Vonage\Client\Credentials\Basic;
use PHPUnit\Framework\TestCase;

class BasicTest extends TestCase
{
    protected $key = 'key';
    protected $secret = 'secret';

    public function testAsArray()
    {
        $credentials = new Basic($this->key, $this->secret);

        $array = $credentials->asArray();
        $this->assertEquals($this->key,    $array['api_key']);
        $this->assertEquals($this->secret, $array['api_secret']);
    }

    public function testArrayAccess()
    {
        $credentials = new Basic($this->key, $this->secret);

        $this->assertEquals($this->key,    $credentials['api_key']);
        $this->assertEquals($this->secret, $credentials['api_secret']);
    }

    public function testProperties()
    {
        $credentials = new Basic($this->key, $this->secret);

        $this->assertEquals($this->key,    $credentials->api_key);
        $this->assertEquals($this->secret, $credentials->api_secret);
    }
}
