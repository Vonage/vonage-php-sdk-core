<?php

declare(strict_types=1);

namespace Vonage\Messages\Webhook;

use InvalidArgumentException;
use Vonage\Webhook\Factory as WebhookFactory;

class Factory extends WebhookFactory
{
    protected static array $classMap = [
        'sms' => 'Vonage\Messages\Webhook\InboundSMS',
        'mms' => 'Vonage\Messages\Webhook\InboundMMS',
        'rcs' => 'Vonage\Messages\Webhook\InboundRCS',
        'whatsapp' => 'Vonage\Messages\Webhook\InboundWhatsApp',
        'messenger' => 'Vonage\Messages\Webhook\InboundMessenger',
        'viber_service' => 'Vonage\Messages\Webhook\InboundViber'
    ];

    public static function createFromArray(array $data): mixed
    {
        if (!isset($data['channel'])) {
            throw new InvalidArgumentException("The 'channel' key is missing in the incoming data.");
        }

        $channel = $data['channel'];

        if (!array_key_exists($channel, self::$classMap)) {
            throw new InvalidArgumentException("Unable to determine incoming webhook type for channel: {$channel}");
        }

        return new self::$classMap[$channel]();
    }
}
