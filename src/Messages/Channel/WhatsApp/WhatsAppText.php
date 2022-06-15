<?php

namespace Vonage\Messages\Channel\WhatsApp;

use Vonage\Messages\MessageTraits\TextTrait;
use Vonage\Messages\Channel\BaseMessage;

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
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $returnArray['text'] = $this->getText();

        return $returnArray;
    }
}
