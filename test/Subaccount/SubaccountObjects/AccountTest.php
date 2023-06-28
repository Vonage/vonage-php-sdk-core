<?php

namespace VonageTest\Subaccount\SubaccountObjects;

use Vonage\Subaccount\SubaccountObjects\Account;
use VonageTest\VonageTestCase;

class AccountTest extends VonageTestCase
{
    public function testToArray(): void
    {
        $instance = new Account();
        $instance->setApiKey('abc123')
                 ->setName('John Doe')
                 ->setPrimaryAccountApiKey('def456')
                 ->setUsePrimaryAccountBalance(true)
                 ->setCreatedAt('2023-06-26T10:23:59Z')
                 ->setSuspended(false)
                 ->setBalance(100.0)
                 ->setCreditLimit(500.0);

        $expectedArray = [
            'api_key' => 'abc123',
            'name' => 'John Doe',
            'primary_account_api_key' => 'def456',
            'use_primary_account_balance' => true,
            'created_at' => '2023-06-26T10:23:59Z',
            'suspended' => false,
            'balance' => 100.0,
            'credit_limit' => 500.0
        ];

        $this->assertEquals($expectedArray, $instance->toArray());
    }

    public function testGettersAndSetters(): void
    {
        $instance = new Account();

        $instance->setApiKey('abc123');
        $this->assertEquals('abc123', $instance->getApiKey());

        $instance->setName('John Doe');
        $this->assertEquals('John Doe', $instance->getName());

        $instance->setPrimaryAccountApiKey('def456');
        $this->assertEquals('def456', $instance->getPrimaryAccountApiKey());

        $instance->setUsePrimaryAccountBalance(true);
        $this->assertTrue($instance->getUsePrimaryAccountBalance());

        $instance->setCreatedAt('2023-06-26T10:23:59Z');
        $this->assertEquals('2023-06-26T10:23:59Z', $instance->getCreatedAt());

        $instance->setSuspended(false);
        $this->assertFalse($instance->getSuspended());

        $instance->setBalance(100.0);
        $this->assertEquals(100.0, $instance->getBalance());

        $instance->setCreditLimit(500.0);
        $this->assertEquals(500.0, $instance->getCreditLimit());
    }
}
