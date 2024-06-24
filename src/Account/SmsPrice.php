<?php

declare(strict_types=1);

namespace Vonage\Account;
class SmsPrice extends Price
{
    /**
     * @var string
     */
    protected $priceMethod = 'getOutboundSmsPrice';
}
