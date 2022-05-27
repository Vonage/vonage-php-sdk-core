<?php

namespace Vonage\Messages\MessageType\WhatsApp;

use Vonage\Messages\MessageTraits\TextTrait;
use Vonage\Messages\MessageType\BaseMessage;

class WhatsAppText extends BaseMessage
{
    use TextTrait;

    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_TEXT;
    protected string $channel = 'whatsapp';

    public function __construct(
        string $to,
        string $from,
        string $text
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->text = $text;
    }

    public function toArray(): array
    {
        return [
            'message_type' => $this->getSubType(),
            'text' => $this->getText(),
            'to' => $this->getTo(),
            'from' => $this->getFrom(),
            'channel' => $this->getChannel(),
            'client_ref' => $this->getClientRef()
        ];
    }
}
