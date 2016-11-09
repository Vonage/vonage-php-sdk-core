<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Client\Credentials;
use Nexmo\Client\Credentials\Keypair;

class KeypairTest extends \PHPUnit_Framework_TestCase
{
    protected $key;
    protected $application = 'c90ddd99-9a5d-455f-8ade-dde4859e590e';

    public function setUp()
    {
        $this->key = file_get_contents(__DIR__ . '/test.key');
    }

    public function testAsArray()
    {
        $credentials = new Keypair($this->key, $this->application);

        $array = $credentials->asArray();
        $this->assertEquals($this->key,    $array['key']);
        $this->assertEquals($this->application, $array['application']);
    }

    public function testArrayAccess()
    {
        $credentials = new Keypair($this->key, $this->application);

        $this->assertEquals($this->key,    $credentials['key']);
        $this->assertEquals($this->application, $credentials['application']);
    }

    public function testProperties()
    {
        $credentials = new Keypair($this->key, $this->application);

        $this->assertEquals($this->key,    $credentials->key);
        $this->assertEquals($this->application, $credentials->application);
    }

    public function testGetJWT()
    {
        $credentials = new Keypair($this->key, $this->application);
        $jwt = $credentials->generateJwt();
        $this->markTestIncomplete('generated JWT, but not tested as valid');
    }
}
