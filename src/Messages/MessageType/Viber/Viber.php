<?php

namespace Vonage\Messages\MessageType;

class Viber extends BaseMessage
{
    protected array $validSubtypes = [
        BaseMessage::MESSAGES_SUBTYPE_TEXT,
        BaseMessage::MESSAGES_SUBTYPE_IMAGE
    ];

    private string $text;

    protected string $channel = 'viber_service';

    public function __construct(
        string $to,
        string $from,
        string $message,
        string $subtype = BaseMessage::MESSAGES_SUBTYPE_TEXT
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->text = $message;
        $this->subtype = $subtype;
    }

    public function toArray(): array
    {
        // TODO: Implement toArray() method.
    }
}