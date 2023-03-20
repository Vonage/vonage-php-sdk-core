<?php

namespace Vonage\Verify2\Webhook;
use Vonage\Verify2\VerifyObjects\VerifyEvent;

class Factory extends \Vonage\Webhook\Factory
{

    public static function createFromArray(array $data): VerifyEvent
    {
        return new VerifyEvent($data);
    }
}