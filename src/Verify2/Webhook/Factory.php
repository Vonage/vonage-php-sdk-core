<?php

namespace Vonage\Verify2\Webhook;

use Vonage\Verify2\VerifyObjects\VerifyEvent;
use Vonage\Verify2\VerifyObjects\VerifySilentAuthUpdate;
use Vonage\Verify2\VerifyObjects\VerifyStatusUpdate;

class Factory extends \Vonage\Webhook\Factory
{
    /**
     * Warning: This logic is fairly brittle, since there are no current better ways of determining
     * the type of event or update.
     */
    public static function createFromArray(array $data): VerifyStatusUpdate|VerifySilentAuthUpdate|VerifyEvent
    {
        if (array_key_exists('action', $data)) {
            return new VerifySilentAuthUpdate($data);
        }

        if (array_key_exists('price', $data)) {
            return new VerifyStatusUpdate($data);
        }

        if (array_key_exists('triggered_at', $data)) {
            return new VerifyEvent($data);
        }

        throw new \OutOfBoundsException('Could not create Verify2 Object from payload');
    }
}
