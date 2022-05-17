<?php

namespace Vonage\Messages\MessageType;

class SMS extends BaseMessage
{
    protected string $subtype = BaseMessage::MESSAGES_SUBTYPE_TEXT;

    protected array $validSubtypes = [
        BaseMessage::MESSAGES_SUBTYPE_TEXT
    ];

    protected string $channel = 'sms';
}