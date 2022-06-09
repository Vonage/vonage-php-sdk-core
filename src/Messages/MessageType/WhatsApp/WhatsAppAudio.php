<?php

namespace Vonage\Messages\MessageType\WhatsApp;

use Vonage\Messages\MessageObjects\AudioObject;
use Vonage\Messages\MessageType\BaseMessage;

class WhatsAppAudio extends BaseMessage
{
    protected string $channel = 'whatsapp';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_AUDIO;
    protected AudioObject $audioObject;

    public function __construct(
        string $to,
        string $from,
        AudioObject $audioObject
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->audioObject = $audioObject;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $returnArray['audio'] = $this->audioObject->toArray();

        return $returnArray;
    }
}