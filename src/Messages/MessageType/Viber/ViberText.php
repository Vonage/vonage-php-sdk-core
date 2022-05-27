<?php

namespace Vonage\Messages\MessageType\Viber;

use Vonage\Messages\MessageTraits\TextTrait;
use Vonage\Messages\MessageType\BaseMessage;

class ViberText extends BaseMessage
{
    use TextTrait;
    use ViberServiceObjectTrait;

    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_TEXT;
    protected string $channel = 'viber_service';

    public function __construct(
        string $to,
        string $from,
        string $message,
        ?string $category = null,
        ?int $ttl = null,
        ?string $type = null
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->text = $message;
        $this->category = $category;
        $this->ttl = $ttl;
        $this->type = $type;
    }

    public function toArray(): array
    {
        $returnArray = [
            'message_type' => $this->subType,
            'text' => $this->getText(),
            'to' => $this->getTo(),
            'from' => $this->getFrom(),
            'channel' => $this->getChannel(),
            'client_ref' => $this->getClientRef()
        ];

        if ($this->requiresViberServiceObject()) {
            $returnArray['viber_service']['category'] = $this->getCategory();
            $returnArray['viber_service']['ttl'] = $this->getTtl();
            $returnArray['viber_service']['type'] = $this->getType();
        }

        return array_filter($returnArray);
    }
}
