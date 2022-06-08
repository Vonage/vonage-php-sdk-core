<?php

namespace Vonage\Messages\MessageType\WhatsApp;

use Vonage\Messages\MessageObjects\VideoObject;
use Vonage\Messages\MessageType\BaseMessage;

class WhatsAppVideo extends BaseMessage
{
    protected string $channel = 'whatsapp';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_VIDEO;
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
        $returnArray = $this->baseMessageArrayOutput();
        $returnArray['video'] = $this->videoObject->toArray();

        return $returnArray;
    }
}