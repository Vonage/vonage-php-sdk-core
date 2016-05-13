<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Account\Application;


use Nexmo\Account\Application\Client;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    protected $nexmoClient;

    /**
     * @var Client
     */
    protected $applicationClient;

    public function setUp()
    {
        $this->nexmoClient = $this->prophesize('Nexmo\Client');
        $this->applicationClient = new Client();
        $this->applicationClient->setClient($this->nexmoClient->reveal());
    }

    public function testSomething()
    {
        
    }

}
