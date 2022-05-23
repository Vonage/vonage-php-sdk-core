<?php

namespace Vonage\Messages\MessageType\SMS;

use Vonage\Messages\MessageType\BaseMessage;

class SMSText extends BaseMessage
{
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_TEXT;

    protected string $channel = 'sms';

    private string $text;

    public function __construct(
        string $to,
        string $from,
        string $message
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->text = $message;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function toArray(): array
    {
        return [
            'message_type' => $this->subType,
            'text' => $this->text,
            'to' => $this->getTo(),
            'from' => $this->getFrom(),
            'channel' => $this->getChannel(),
            'client_ref' => $this->getSubType()
        ];
    }
}
