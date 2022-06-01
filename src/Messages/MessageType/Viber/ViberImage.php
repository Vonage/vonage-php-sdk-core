<?php

namespace Vonage\Messages\MessageType\Viber;

use Vonage\Messages\MessageObjects\ImageObject;
use Vonage\Messages\MessageType\BaseMessage;

class ViberImage extends BaseMessage
{
    use ViberServiceObjectTrait;

    protected string $channel = 'viber_service';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_IMAGE;
    protected ImageObject $image;

    public function __construct(
        string $to,
        string $from,
        ImageObject $image,
        ?string $category = null,
        ?int $ttl = null,
        ?string $type = null
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->image = $image;
        $this->category = $category;
        $this->ttl = $ttl;
        $this->type = $type;
    }

    public function toArray(): array
    {
        $returnArray = [
            'message_type' => $this->getSubType(),
            'image' => $this->image->toArray(),
            'to' => $this->getTo(),
            'from' => $this->getFrom(),
            'channel' => $this->getChannel(),
            'client_ref' => $this->getClientRef(),
        ];

        if ($this->requiresViberServiceObject()) {
            $returnArray['viber_service']['category'] = $this->getCategory();
            $returnArray['viber_service']['ttl'] = $this->getTtl();
            $returnArray['viber_service']['type'] = $this->getType();
        }

        return array_filter($returnArray);
    }
}
