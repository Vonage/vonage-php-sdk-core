<?php

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
    protected Balance $balance;

    public function setUp(): void
    {
        $this->balance = new Balance(12.99, false);
    }

    public function testObjectAccess(): void
    {
        $this->assertEquals(12.99, $this->balance->getBalance());
        $this->assertEquals(false, $this->balance->getAutoReload());
    }
}
