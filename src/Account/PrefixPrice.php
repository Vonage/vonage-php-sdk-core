<?php

declare(strict_types=1);

namespace Vonage\Account;

use Vonage\Client\Exception\Exception as ClientException;

class PrefixPrice extends Price
{
    protected string $priceMethod = 'getPrefixPrice';

    public function getCurrency(): ?string
    {
        throw new ClientException('Currency is unavailable from this endpoint');
    }
}
