<?php
/**
 * @author Tim Lytle <tim@timlytle.net>
 */

use Nexmo\Credentials\OAuth;

class OAuthTest extends PHPUnit_Framework_TestCase
{
    protected $appToken     = 'appToken';
    protected $appSecret    = 'appSecret';
    protected $clientToken  = 'clientToken';
    protected $clientSecret = 'clientSecret';

    /**
     * @var OAuth
     */
    protected $credentials;

    public function setUp()
    {
        $this->credentials = new OAuth($this->appToken, $this->appSecret, $this->clientToken, $this->clientSecret);
    }

    public function testCredentialArray()
    {
        $array = $this->credentials->getCredentials();
        $this->assertEquals($this->clientToken,     $array['token']);
        $this->assertEquals($this->clientSecret,    $array['token_secret']);
        $this->assertEquals($this->appToken,  $array['consumer_key']);
        $this->assertEquals($this->appSecret, $array['consumer_secret']);
    }
}