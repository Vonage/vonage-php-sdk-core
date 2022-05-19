<?php

namespace Vonage\Messages\MessageType;

class SMS extends BaseMessage
{
    protected string $subtype = BaseMessage::MESSAGES_SUBTYPE_TEXT;

    protected array $validSubtypes = [
        BaseMessage::MESSAGES_SUBTYPE_TEXT
    ];

    protected string $channel = 'sms';

    private string $text;

    public function __construct(string $to, string $from, string $message)
    {
        $this->to = $to;
        $this->from = $from;
        $this->text = $message;
    }

    public function toArray(): array
    {
        // TODO: Implement toArray() method.
    }
}
