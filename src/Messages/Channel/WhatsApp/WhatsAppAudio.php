<?php

namespace Vonage\Messages\Channel\WhatsApp;

use Vonage\Messages\MessageObjects\AudioObject;
use Vonage\Messages\Channel\BaseMessage;
use Vonage\Messages\MessageTraits\ContextTrait;

class WhatsAppAudio extends BaseMessage
{
    use ContextTrait;

    protected string $channel = 'whatsapp';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_AUDIO;
    protected bool $validatesE164 = true;

    public function __construct(
        string $to,
        string $from,
        protected AudioObject $audioObject
    ) {
        $this->to = $to;
        $this->from = $from;
    }

    public function validatesE164(): bool
    {
        return $this->validatesE164;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $returnArray['audio'] = $this->audioObject->toArray();
        $returnArray['context'] = $this->context ?? null;

        return $returnArray;
    }
}
