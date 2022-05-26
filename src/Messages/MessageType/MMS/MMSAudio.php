<?php

namespace Vonage\Messages\MessageType\MMS;

use Vonage\Messages\MessageObjects\AudioObject;
use Vonage\Messages\MessageType\BaseMessage;

class MMSAudio extends BaseMessage
{
    protected string $channel = 'mms';
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
        return [
            'message_type' => $this->getSubType(),
            'audio' => $this->audioObject->toArray(),
            'to' => $this->getTo(),
            'from' => $this->getFrom(),
            'channel' => $this->getChannel(),
            'client_ref' => $this->getClientRef()
        ];
    }
}