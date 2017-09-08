<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Account;

use Nexmo\Account\Balance;

class BalanceTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->balance = new Balance('12.99', false);
    }

    public function testObjectAccess()
    {
        $this->assertEquals("12.99", $this->balance->getBalance());
        $this->assertEquals(false, $this->balance->getAutoReload());
    }

    public function testArrayAccess()
    {
        $this->assertEquals("12.99", $this->balance['balance']);
        $this->assertEquals(false, $this->balance['auto_reload']);
    }
}
