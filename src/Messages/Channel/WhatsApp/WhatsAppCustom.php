<?php

namespace Vonage\Messages\Channel\WhatsApp;

use Vonage\Messages\Channel\BaseMessage;

class WhatsAppCustom extends BaseMessage
{
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_CUSTOM;
    protected string $channel = 'whatsapp';

    public function __construct(
        string $to,
        string $from,
        protected array $custom
    ) {
        $this->to = $to;
        $this->from = $from;
    }

    public function setCustom(array $custom): void
    {
        $this->custom = $custom;
    }

    public function getCustom(): array
    {
        return $this->custom;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $returnArray['custom'] = $this->getCustom();

        return $returnArray;
    }
}
