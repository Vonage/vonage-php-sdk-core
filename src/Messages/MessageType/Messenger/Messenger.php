<?php

namespace Vonage\Messages\MessageType\Messenger;

use Vonage\Messages\MessageType\BaseMessage;

class Messenger extends BaseMessage
{
    protected array $validSubtypes = [
        BaseMessage::MESSAGES_SUBTYPE_TEXT,
        BaseMessage::MESSAGES_SUBTYPE_IMAGE,
        BaseMessage::MESSAGES_SUBTYPE_AUDIO,
        BaseMessage::MESSAGES_SUBTYPE_VIDEO
    ];

    private string $text;

    protected string $channel = 'messenger';

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
