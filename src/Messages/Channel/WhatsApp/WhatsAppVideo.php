<?php

namespace Vonage\Messages\Channel\WhatsApp;

use Vonage\Messages\MessageObjects\VideoObject;
use Vonage\Messages\Channel\BaseMessage;

class WhatsAppVideo extends BaseMessage
{
    protected string $channel = 'whatsapp';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_VIDEO;

    public function __construct(
        string $to,
        string $from,
        protected VideoObject $videoObject
    ) {
        $this->to = $to;
        $this->from = $from;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $returnArray['video'] = $this->videoObject->toArray();

        return $returnArray;
    }
}