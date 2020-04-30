<?php
declare(strict_types=1);
namespace Nexmo\Account;

class SmsPrice extends Price
{
    /**
     * @var string
     */
    protected $priceMethod = 'getOutboundSmsPrice';
}
