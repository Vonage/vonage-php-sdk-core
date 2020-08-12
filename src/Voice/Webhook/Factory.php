<?php
declare(strict_types=1);

namespace Vonage\Voice\Webhook;

use Vonage\Webhook\Factory as WebhookFactory;

class Factory extends WebhookFactory
{
    public static function createFromArray(array $data)
    {
        if (array_key_exists('status', $data)) {
            return new Event($data);
        }

        // Answer webhooks have no defining type other than length and keys
        if (count($data) === 4 && array_diff(array_keys($data), ['to', 'from', 'uuid', 'conversation_uuid']) === []) {
            return new Answer($data);
        }

        if (array_key_exists('type', $data)) {
            switch ($data['type']) {
                case 'transfer':
                    return new Transfer($data);
            }
        }

        if (array_key_exists('recording_url', $data)) {
            return new Record($data);
        }

        if (array_key_exists('reason', $data)) {
            return new Error($data);
        }

        if (array_key_exists('payload', $data)) {
            return new Notification($data);
        }

        if (array_key_exists('speech', $data) || array_key_exists('dtmf', $data)) {
            return new Input($data);
        }

        throw new \InvalidArgumentException('Unable to detect incoming webhook type');
    }
}
