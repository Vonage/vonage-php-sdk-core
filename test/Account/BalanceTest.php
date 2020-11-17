<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Account;

use PHPUnit\Framework\TestCase;
use Vonage\Account\Balance;
use Vonage\Client\Exception\Exception as ClientException;

class BalanceTest extends TestCase
{
    /**
     * @var Balance
     */
    protected $balance;

    public function setUp(): void
    {
        $this->balance = new Balance('12.99', false);
    }

    public function testObjectAccess(): void
    {
        $this->assertEquals("12.99", $this->balance->getBalance());
        $this->assertEquals(false, $this->balance->getAutoReload());
    }

    public function testArrayAccess(): void
    {
        $this->assertEquals("12.99", @$this->balance['balance']);
        $this->assertEquals(false, @$this->balance['auto_reload']);
    }

    public function testJsonSerialize(): void
    {
        $data = $this->balance->jsonSerialize();

        $this->assertSame('12.99', $data['balance']);
        $this->assertFalse($data['auto_reload']);
    }

    public function testJsonUnserialize(): void
    {
        $data = ['value' => '5.00', 'autoReload' => false];

        $balance = new Balance('1.99', true);
        $balance->fromArray($data);

        $this->assertSame($data['value'], @$balance['balance']);
        $this->assertSame($data['autoReload'], @$balance['auto_reload']);
    }

    public function testActsLikeArray(): void
    {
        $this->assertSame('12.99', @$this->balance['balance']);
        $this->assertTrue(@isset($this->balance['balance']));
    }

    public function testCannotRemoveArrayKey(): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Balance is read only');

        unset($this->balance['balance']);
    }

    public function testCannotDirectlySetArrayKey(): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Balance is read only');

        $this->balance['balance'] = '5.00';
    }

    public function testMakeSureDataIsPubliclyVisible(): void
    {
        $this->assertSame('12.99', @$this->balance->data['balance']);
    }
}
