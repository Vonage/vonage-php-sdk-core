<?php

namespace Vonage\Messages\MessageType;

class WhatsApp extends BaseMessage
{
    protected string $subtype = BaseMessage::MESSAGES_SUBTYPE_TEXT;

    protected array $validSubtypes = [
        BaseMessage::MESSAGES_SUBTYPE_TEXT,
        BaseMessage::MESSAGES_SUBTYPE_IMAGE,
        BaseMessage::MESSAGES_SUBTYPE_AUDIO,
        BaseMessage::MESSAGES_SUBTYPE_VIDEO,
        BaseMessage::MESSAGES_SUBTYPE_FILE,
        BaseMessage::MESSAGES_SUBTYPE_TEMPLATE,
        BaseMessage::MESSAGES_SUBTYPE_CUSTOM
    ];

    protected string $channel = 'whatsapp';

    public function toArray(): array
    {
        // TODO: Implement toArray() method.
    }
}