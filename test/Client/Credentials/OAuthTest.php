<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Client\Credentials;

use \Vonage\Client\Credentials\OAuth;
use PHPUnit\Framework\TestCase;

class OAuthTest extends TestCase
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
        $credentials = new \Vonage\Client\Credentials\OAuth($this->appToken, $this->appSecret, $this->clientToken, $this->clientSecret);

        $this->assertEquals($this->clientToken,     $credentials['token']);
        $this->assertEquals($this->clientSecret,    $credentials['token_secret']);
        $this->assertEquals($this->appToken,        $credentials['consumer_key']);
        $this->assertEquals($this->appSecret,       $credentials['consumer_secret']);
    }

    public function testProperties()
    {
        $credentials = new \Vonage\Client\Credentials\OAuth($this->appToken, $this->appSecret, $this->clientToken, $this->clientSecret);

        $this->assertEquals($this->clientToken,     $credentials->token);
        $this->assertEquals($this->clientSecret,    $credentials->token_secret);
        $this->assertEquals($this->appToken,        $credentials->consumer_key);
        $this->assertEquals($this->appSecret,       $credentials->consumer_secret);
    }
}