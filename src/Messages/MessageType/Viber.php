<?php

namespace Vonage\Messages\MessageType;

class Viber extends BaseMessage
{
    protected string $subtype = BaseMessage::MESSAGES_SUBTYPE_TEXT;

    protected array $validSubtypes = [
        BaseMessage::MESSAGES_SUBTYPE_TEXT,
        BaseMessage::MESSAGES_SUBTYPE_IMAGE
    ];

    protected string $channel = 'viber_service';
}