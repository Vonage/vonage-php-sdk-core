<?php

namespace Vonage\Messages\Channel\WhatsApp;

use Vonage\Messages\Channel\BaseMessage;
use Vonage\Messages\Channel\WhatsApp\MessageObjects\StickerObject;
use Vonage\Messages\MessageTraits\ContextTrait;

class WhatsAppSticker extends BaseMessage
{
    use ContextTrait;

    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_STICKER;
    protected string $channel = 'whatsapp';

    public function __construct(
        string $to,
        string $from,
        protected StickerObject $sticker
    ) {
        $this->to = $to;
        $this->from = $from;
    }

    public function getSticker(): StickerObject
    {
        return $this->sticker;
    }

    public function setSticker(StickerObject $sticker): static
    {
        $this->sticker = $sticker;

        return $this;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $returnArray['sticker'] = $this->getSticker()->toArray();

        if (!is_null($this->context)) {
            $returnArray['context'] = $this->context;
        }

        return $returnArray;
    }
}
