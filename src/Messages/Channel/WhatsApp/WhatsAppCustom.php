<?php

namespace Vonage\Messages\Channel\WhatsApp;

use Vonage\Messages\Channel\BaseMessage;
use Vonage\Messages\MessageTraits\ContextTrait;

class WhatsAppCustom extends BaseMessage
{
    use ContextTrait;

    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_CUSTOM;
    protected string $channel = 'whatsapp';
    protected bool $validatesE164 = true;

    public function __construct(
        string $to,
        string $from,
        protected array $custom
    ) {
        $this->to = $to;
        $this->from = $from;
    }

    public function validatesE164(): bool
    {
        return $this->validatesE164;
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
        $returnArray['context'] = $this->context ?? null;

        return $returnArray;
    }
}
