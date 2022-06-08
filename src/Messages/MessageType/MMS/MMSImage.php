<?php

namespace Vonage\Messages\MessageType\MMS;

use Vonage\Messages\MessageObjects\ImageObject;
use Vonage\Messages\MessageType\BaseMessage;

class MMSImage extends BaseMessage
{
    protected string $channel = 'mms';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_IMAGE;
    protected ImageObject $image;

    public function __construct(
        string $to,
        string $from,
        ImageObject $image
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->image = $image;
    }

    public function toArray(): array
    {
        $returnArray = $this->baseMessageArrayOutput();
        $returnArray['image'] = $this->image->toArray();

        return $returnArray;
    }
}