<?php

namespace Vonage\Messages\MessageType;

class MMS extends BaseMessage
{
    protected array $validSubtypes = [
        BaseMessage::MESSAGES_SUBTYPE_IMAGE,
        BaseMessage::MESSAGES_SUBTYPE_VCARD,
        BaseMessage::MESSAGES_SUBTYPE_AUDIO,
        BaseMessage::MESSAGES_SUBTYPE_VIDEO
    ];

    protected string $channel = 'mms';

    public function __construct(
        string $to,
        string $from,
        string $subtype = BaseMessage::MESSAGES_SUBTYPE_IMAGE
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->subtype = $subtype;
    }

    public function toArray(): array
    {
        // TODO: Implement toArray() method.
    }
}