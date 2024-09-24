<?php

declare(strict_types=1);

namespace Vonage\Account;

class VoicePrice extends Price
{
    protected string $priceMethod = 'getOutboundVoicePrice';
}
