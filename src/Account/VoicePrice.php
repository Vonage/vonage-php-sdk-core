<?php
declare(strict_types=1);

namespace Nexmo\Account;

class VoicePrice extends Price
{
    /**
     * @var string
     */
    protected $priceMethod = 'getOutboundVoicePrice';
}
