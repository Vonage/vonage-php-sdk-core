<?php

namespace Vonage\Messages\MessageType\Messenger;

use Vonage\Messages\MessageObjects\ImageObject;
use Vonage\Messages\MessageType\BaseMessage;

class MessengerImage extends BaseMessage
{
    use MessengerObjectTrait;

    protected string $channel = 'messenger';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_IMAGE;
    protected ImageObject $image;

    public function __construct(
        string $to,
        string $from,
        ImageObject $image,
        string $category,
        string $tag = ''
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->image = $image;
        $this->category = $category;
        $this->tag = $tag;
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
            'messenger' => $this->getMessengerObject()
        ];
    }
}
