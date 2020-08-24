<?php

namespace Vonage\Account;

use \Exception;

class PrefixPrice extends Price
{
    protected $priceMethod = 'getPrefixPrice';

    public function getCurrency()
    {
        throw new Exception('Currency is unavailable from this endpoint');
    }
}
