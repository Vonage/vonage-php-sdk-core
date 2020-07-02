<?php
declare(strict_types=1);

namespace Nexmo\SMS\Webhook;

use Nexmo\SMS\Webhook\InboundSMS;
use Nexmo\Webhook\Factory as WebhookFactory;

class Factory extends WebhookFactory
{
    public static function createFromArray(array $data)
    {
        return new InboundSMS($data);
    }
}
