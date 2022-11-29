<?php

namespace Vonage\Messages\Channel\MMS;

use Vonage\Messages\MessageObjects\ImageObject;
use Vonage\Messages\Channel\BaseMessage;

class MMSImage extends BaseMessage
{
    protected string $channel = 'mms';
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