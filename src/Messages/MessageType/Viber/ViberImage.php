<?php

namespace Vonage\Messages\MessageType\Viber;

use Vonage\Messages\MessageObjects\ImageObject;
use Vonage\Messages\MessageType\BaseMessage;

class ViberImage extends BaseMessage
{
    protected string $channel = 'viber_service';
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
        return [
            'message_type' => $this->getSubType(),
            'image' => $this->image->toArray(),
            'to' => $this->getTo(),
            'from' => $this->getFrom(),
            'channel' => $this->getChannel(),
            'client_ref' => $this->getClientRef(),
        ];
    }
}
