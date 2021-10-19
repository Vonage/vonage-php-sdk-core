<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Account;

use VonageTest\VonageTestCase;
use Vonage\Account\Balance;
use Vonage\Client\Exception\Exception as ClientException;

class BalanceTest extends VonageTestCase
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

    public function testJsonSerialize(): void
    {
        $data = $this->balance->jsonSerialize();

        $this->assertSame('12.99', $data['balance']);
        $this->assertFalse($data['auto_reload']);
    }

    public function testDoesNotActLikeArray(): void
    {
        $this->expectErrorMessage('Cannot use object of type Vonage\Account\Balance as array');
        $balance = $this->balance['balance'];
    }

    public function testCannotSetArrayKey(): void
    {
        $this->expectErrorMessage('Cannot use object of type Vonage\Account\Balance as array');
        $newBalance = '14.99';
        $this->balance['balance'] = $newBalance;
    }

    public function testCannotSetArrayKeyInsideStorage(): void
    {
        $this->expectErrorMessage('Cannot access protected property Vonage\Account\Balance::$data');
        $newBalance = '14.99';
        $this->balance->data['balance'] = $newBalance;
    }
}
