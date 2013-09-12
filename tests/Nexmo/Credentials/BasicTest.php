<?php
/**
 * @author Tim Lytle <tim@timlytle.net>
 */

use \Nexmo\Credentials\Basic;

class BasicTest extends PHPUnit_Framework_TestCase
{
    protected $key = 'key';
    protected $secret = 'secret';

    /**
     * @var Basic
     */
    protected $credentials;

    public function setUp()
    {
        $this->credentials = new Basic($this->key, $this->secret);
    }

    public function testCredentialArray()
    {
        $array = $this->credentials->getCredentials();
        $this->assertEquals($this->key,    $array['key']);
        $this->assertEquals($this->secret, $array['secret']);
    }
}
