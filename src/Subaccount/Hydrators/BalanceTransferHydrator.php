<?php

declare(strict_types=1);

namespace Vonage\Subaccount\Hydrators;

use Vonage\Entity\Hydrator\HydratorInterface;
use Vonage\Subaccount\SubaccountObjects\Account;
use Vonage\Subaccount\SubaccountObjects\BalanceTransfer;
use Vonage\Subaccount\SubaccountObjects\CreditTransfer;

class BalanceTransferHydrator implements HydratorInterface
{
    public function hydrate(array $data)
    {
        $subaccount = new BalanceTransfer();
        return $this->hydrateObject($data, $subaccount);
    }

    public function hydrateObject(array $data, $object)
    {
        $object->fromArray($data);
        return $object;
    }
}
