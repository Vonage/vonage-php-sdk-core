<?php

namespace Vonage\Messages\MessageType\Messenger;

use Vonage\Messages\MessageTraits\TextTrait;
use Vonage\Messages\MessageType\BaseMessage;

class MessengerText extends BaseMessage
{
    use TextTrait;
    use MessengerObjectTrait;

    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_TEXT;
    protected string $channel = 'messenger';

    public function __construct(
        string $to,
        string $from,
        string $text,
        ?string $category = null,
        ?string $tag = null
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->text = $text;
        $this->category = $category;
        $this->tag = $tag;
    }

    public function toArray(): array
    {
        $returnArray = [
            'message_type' => $this->getSubType(),
            'text' => $this->getText(),
            'to' => $this->getTo(),
            'from' => $this->getFrom(),
            'channel' => $this->getChannel(),
            'client_ref' => $this->getClientRef(),
        ];

        if ($this->requiresMessengerObject()) {
            $returnArray['messenger'] = $this->getMessengerObject();
        }

        return $returnArray;
    }
}
