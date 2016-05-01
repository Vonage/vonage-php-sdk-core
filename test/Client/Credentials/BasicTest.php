<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Client\Credentials;
use \Nexmo\Client\Credentials\Basic;

class BasicTest extends \PHPUnit_Framework_TestCase
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
