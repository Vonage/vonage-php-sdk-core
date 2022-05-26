<?php

namespace Vonage\Messages\MessageType\MMS;

use Vonage\Messages\MessageObjects\AudioObject;
use Vonage\Messages\MessageObjects\VideoObject;
use Vonage\Messages\MessageType\BaseMessage;

class MMSVideo extends BaseMessage
{
    protected string $channel = 'mms';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_AUDIO;
    protected VideoObject $videoObject;

    public function __construct(
        string $to,
        string $from,
        VideoObject $videoObject
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->videoObject = $videoObject;
    }

    public function toArray(): array
    {
        return [
            'message_type' => $this->getSubType(),
            'video' => $this->videoObject->toArray(),
            'to' => $this->getTo(),
            'from' => $this->getFrom(),
            'channel' => $this->getChannel(),
            'client_ref' => $this->getClientRef()
        ];
    }
}