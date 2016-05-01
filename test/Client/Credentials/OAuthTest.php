<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Client\Credentials;
use \Nexmo\Client\Credentials\OAuth;

class OAuthTest extends \PHPUnit_Framework_TestCase
{
    protected $appToken     = 'appToken';
    protected $appSecret    = 'appSecret';
    protected $clientToken  = 'clientToken';
    protected $clientSecret = 'clientSecret';

    public function testAsArray()
    {
        $credentials = new OAuth($this->appToken, $this->appSecret, $this->clientToken, $this->clientSecret);
        
        $array = $credentials->asArray();
        $this->assertEquals($this->clientToken,     $array['token']);
        $this->assertEquals($this->clientSecret,    $array['token_secret']);
        $this->assertEquals($this->appToken,        $array['consumer_key']);
        $this->assertEquals($this->appSecret,       $array['consumer_secret']);
    }

    public function testArrayAccess()
    {
        $credentials = new \Nexmo\Client\Credentials\OAuth($this->appToken, $this->appSecret, $this->clientToken, $this->clientSecret);

        $this->assertEquals($this->clientToken,     $credentials['token']);
        $this->assertEquals($this->clientSecret,    $credentials['token_secret']);
        $this->assertEquals($this->appToken,        $credentials['consumer_key']);
        $this->assertEquals($this->appSecret,       $credentials['consumer_secret']);
    }

    public function testProperties()
    {
        $credentials = new \Nexmo\Client\Credentials\OAuth($this->appToken, $this->appSecret, $this->clientToken, $this->clientSecret);

        $this->assertEquals($this->clientToken,     $credentials->token);
        $this->assertEquals($this->clientSecret,    $credentials->token_secret);
        $this->assertEquals($this->appToken,        $credentials->consumer_key);
        $this->assertEquals($this->appSecret,       $credentials->consumer_secret);
    }
}