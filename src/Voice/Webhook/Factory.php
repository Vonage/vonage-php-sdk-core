<?php
declare(strict_types=1);

namespace Nexmo\Voice\Webhook;

use Nexmo\Webhook\Factory as WebhookFactory;

class Factory extends WebhookFactory
{
    public static function createFromArray(array $data)
    {
        if (array_key_exists('type', $data)) {
            switch ($data['type']) {
                case 'answer':
                    return new Answer($data);
                case 'transfer':
                    return new Transfer($data);
            }
        }

        if (array_key_exists('status', $data)) {
            return new Event($data);
        }
    }
}
