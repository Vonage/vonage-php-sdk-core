<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test\Account;

use PHPUnit\Framework\TestCase;
use Vonage\Account\Balance;
use Vonage\Client\Exception\Exception;

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
        self::assertEquals("12.99", $this->balance->getBalance());
        self::assertEquals(false, $this->balance->getAutoReload());
    }

    public function testArrayAccess(): void
    {
        self::assertEquals("12.99", @$this->balance['balance']);
        self::assertEquals(false, @$this->balance['auto_reload']);
    }

    public function testJsonSerialize(): void
    {
        $data = $this->balance->jsonSerialize();

        self::assertSame('12.99', $data['balance']);
        self::assertFalse($data['auto_reload']);
    }

    public function testJsonUnserialize(): void
    {
        $data = ['value' => '5.00', 'autoReload' => false];

        $balance = new Balance('1.99', true);
        $balance->fromArray($data);

        self::assertSame($data['value'], @$balance['balance']);
        self::assertSame($data['autoReload'], @$balance['auto_reload']);
    }

    public function testActsLikeArray(): void
    {
        self::assertSame('12.99', @$this->balance['balance']);
        self::assertTrue(@isset($this->balance['balance']));
    }

    public function testCannotRemoveArrayKey(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Balance is read only');

        unset($this->balance['balance']);
    }

    public function testCannotDirectlySetArrayKey(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Balance is read only');

        $this->balance['balance'] = '5.00';
    }

    public function testMakeSureDataIsPubliclyVisible(): void
    {
        $data = $this->balance->toArray();
        self::assertSame('12.99', $data['balance']);
    }
}
