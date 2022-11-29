<?php

namespace Vonage\Messages\Channel\WhatsApp;

use Vonage\Messages\MessageObjects\ImageObject;
use Vonage\Messages\Channel\BaseMessage;

class WhatsAppImage extends BaseMessage
{
    protected string $channel = 'whatsapp';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_IMAGE;

    public function __construct(
        string $to,
        string $from,
        protected ImageObject $image
    ) {
        $this->to = $to;
        $this->from = $from;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $returnArray['image'] = $this->image->toArray();

        return $returnArray;
    }
}
