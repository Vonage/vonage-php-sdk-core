<?php

namespace VonageTest\Subaccount\Hydrators;

use Vonage\Subaccount\Hydrators\BalanceTransferHydrator;
use Vonage\Subaccount\SubaccountObjects\BalanceTransfer;
use VonageTest\VonageTestCase;

class BalanceTransferHydratorTest extends VonageTestCase
{
    public function testHydrate(): void
    {
        $hydrator = new BalanceTransferHydrator();

        $data = [
            "balance_transfer_id" => "07b5-46e1-a527-85530e625800",
            "amount" => 123.45,
            "from" => "7c9738e6",
            "to" => "ad6dc56f",
            "reference" => "This gets added to the audit log",
            "created_at" => "2019-03-02T16:34:49Z"
        ];

        $balanceTransfer = $hydrator->hydrate($data);
        $this->assertInstanceOf(BalanceTransfer::class, $balanceTransfer);
        $this->assertEquals("07b5-46e1-a527-85530e625800", $balanceTransfer->getBalanceTransferId());
        $this->assertEquals(123.45, $balanceTransfer->getAmount());
        $this->assertEquals("7c9738e6", $balanceTransfer->getFrom());
        $this->assertEquals("ad6dc56f", $balanceTransfer->getTo());
        $this->assertEquals("This gets added to the audit log", $balanceTransfer->getReference());
        $this->assertEquals("2019-03-02T16:34:49Z", $balanceTransfer->getCreatedAt());
    }
}
