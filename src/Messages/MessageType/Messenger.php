<?php

namespace Vonage\Messages\MessageType;

class Messenger extends BaseMessage
{
    protected string $subtype = BaseMessage::MESSAGES_SUBTYPE_TEXT;

    protected array $validSubtypes = [
        BaseMessage::MESSAGES_SUBTYPE_TEXT,
        BaseMessage::MESSAGES_SUBTYPE_IMAGE,
        BaseMessage::MESSAGES_SUBTYPE_AUDIO,
        BaseMessage::MESSAGES_SUBTYPE_VIDEO
    ];

    protected string $channel = 'messenger';
}