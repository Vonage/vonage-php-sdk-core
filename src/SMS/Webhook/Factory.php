<?php
declare(strict_types=1);

namespace Vonage\SMS\Webhook;

use Vonage\SMS\Webhook\InboundSMS;
use Vonage\Webhook\Factory as WebhookFactory;

class Factory extends WebhookFactory
{
    public static function createFromArray(array $data)
    {
        if (array_key_exists('scts', $data)) {
            return new DeliveryReceipt($data);
        }

        if (count(array_intersect(array_keys($data), InboundSMS::$requiredFields))
            === count(InboundSMS::$requiredFields)
        ) {
            return new InboundSMS($data);
        }
        
        throw new \InvalidArgumentException("Unable to determine incoming webhook type");
    }
}
