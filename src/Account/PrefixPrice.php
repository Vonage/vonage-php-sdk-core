<?php

namespace Nexmo\Account;

use \Exception;

class PrefixPrice extends Price
{
    protected $priceMethod = 'getPrefixPrice';

    public function getCurrency() : string
    {
        throw new Exception('Currency is unavailable from this endpoint');
    }
}
