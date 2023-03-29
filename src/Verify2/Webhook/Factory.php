<?php

namespace Vonage\Verify2\Webhook;

use Vonage\Verify2\VerifyObjects\VerifyEvent;
use Vonage\Verify2\VerifyObjects\VerifySilentAuthEvent;
use Vonage\Verify2\VerifyObjects\VerifyStatusUpdate;
use Vonage\Verify2\VerifyObjects\VerifyWhatsAppInteractiveEvent;

class Factory extends \Vonage\Webhook\Factory
{
    /**
     * Warning: This logic is fairly brittle, since there are no current better ways of determining
     * the type of event or update.
     */
    public static function createFromArray(array $data)
    {
        if ($data['type'] === 'event') {
            if ($data['channel'] === 'silent_auth') {
                return new VerifySilentAuthEvent($data);
            }
            if ($data['channel'] === 'whatsapp_interactive') {
                return new VerifyWhatsAppInteractiveEvent($data);
            }
            return new VerifyEvent($data);
        }

        if ($data['type'] === 'summary') {
            return new VerifyStatusUpdate($data);
        }

        throw new \OutOfBoundsException('Could not create Verify2 Object from payload');
    }
}
