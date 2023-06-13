<?php

namespace VonageTest\Subaccount\Hydrators;

use Vonage\Subaccount\Hydrators\AccountHydrator;
use Vonage\Subaccount\SubaccountObjects\Account;
use PHPUnit\Framework\TestCase;

class AccountHydratorTest extends TestCase
{
    public function testHydrate(): void
    {
        $hydrator = new AccountHydrator();

        $data = [
            "api_key" => "bbe6222f",
            "name" => "Subaccount department A",
            "primary_account_api_key" => "acc6111f",
            "use_primary_account_balance" => true,
            "created_at" => "2018-03-02T16:34:49Z",
            "suspended" => false,
            "balance" => 100.25,
            "credit_limit" => -100.25
        ];

        $account = $hydrator->hydrate($data);

        $this->assertInstanceOf(Account::class, $account);
        $this->assertEquals('bbe6222f', $account->getApiKey());
        $this->assertEquals('Subaccount department A', $account->getName());
    }
}
