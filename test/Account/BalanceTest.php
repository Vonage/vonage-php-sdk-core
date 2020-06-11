<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Account;

use Nexmo\Account\Balance;
use PHPUnit\Framework\TestCase;

class BalanceTest extends TestCase
{
    /**
     * @var Balance
     */
    protected $balance;

    public function setUp()
    {
        $this->balance = new Balance('12.99', false);
    }

    public function testObjectAccess()
    {
        $this->assertEquals("12.99", $this->balance->getBalance());
        $this->assertEquals(false, $this->balance->getAutoReload());
    }

    public function testJsonSerialize()
    {
        $data = $this->balance->jsonSerialize();

        $this->assertSame(12.99, $data['balance']);
        $this->assertSame(false, $data['autoReload']);
    }

    public function testUnserializingFromArray()
    {
        $data = ['value' => 5.00, 'autoReload' => false];

        $balance = new Balance((float) '1.99', true);
        $balance->fromArray($data);

        $this->assertSame($data['value'], $balance->getBalance());
        $this->assertSame($data['autoReload'], $balance->getAutoReload());
    }
}
