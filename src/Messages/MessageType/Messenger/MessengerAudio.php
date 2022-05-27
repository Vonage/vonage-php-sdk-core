<?php

namespace Vonage\Messages\MessageType\Messenger;

use Vonage\Messages\MessageObjects\AudioObject;
use Vonage\Messages\MessageType\BaseMessage;

class MessengerAudio extends BaseMessage
{
    use MessengerObjectTrait;

    protected string $channel = 'messenger';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_AUDIO;
    protected AudioObject $audioObject;

    public function __construct(
        string $to,
        string $from,
        AudioObject $audioObject,
        string $category,
        string $tag = ''
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->audioObject = $audioObject;
        $this->category = $category;
        $this->tag = $tag;
    }

    public function toArray(): array
    {
        return [
            'message_type' => $this->getSubType(),
            'audio' => $this->audioObject->toArray(),
            'to' => $this->getTo(),
            'from' => $this->getFrom(),
            'channel' => $this->getChannel(),
            'client_ref' => $this->getClientRef(),
            'messenger' => $this->getMessengerObject()
        ];
    }
}
