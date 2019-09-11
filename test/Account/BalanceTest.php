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

    public function testArrayAccess()
    {
        $this->assertEquals("12.99", $this->balance['balance']);
        $this->assertEquals(false, $this->balance['auto_reload']);
    }

    public function testJsonSerialize()
    {
        $data = $this->balance->jsonSerialize();

        $this->assertSame('12.99', $data['balance']);
        $this->assertSame(false, $data['auto_reload']);
    }

    public function testJsonUnserialize()
    {
        $data = ['value' => '5.00', 'autoReload' => false];

        $balance = new Balance('1.99', true);
        $balance->jsonUnserialize($data);

        $this->assertSame($data['value'], $balance['balance']);
        $this->assertSame($data['autoReload'], $balance['auto_reload']);
    }

    public function testActsLikeArray()
    {
        $this->assertSame('12.99', $this->balance['balance']);
        $this->assertTrue(isset($this->balance['balance']));
    }

    public function testCannotRemoveArrayKey()
    {
        $this->expectException('Nexmo\Client\Exception\Exception');
        $this->expectExceptionMessage('Balance is read only');

        unset($this->balance['balance']);
    }

    public function testCannotDirectlySetArrayKey()
    {
        $this->expectException('Nexmo\Client\Exception\Exception');
        $this->expectExceptionMessage('Balance is read only');

        $this->balance['balance'] = '5.00';
    }

    public function testMakeSureDataIsPubliclyVisible()
    {
        $this->assertSame('12.99', $this->balance->data['balance']);
    }
}
