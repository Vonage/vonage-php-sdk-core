<?php

namespace Vonage\Messages\Channel\WhatsApp;

use Vonage\Messages\MessageObjects\ImageObject;
use Vonage\Messages\Channel\BaseMessage;
use Vonage\Messages\MessageTraits\ContextTrait;

class WhatsAppImage extends BaseMessage
{
    use ContextTrait;

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

        if (!is_null($this->context)) {
            $returnArray['context'] = $this->context;
        }

        return $returnArray;
    }
}
