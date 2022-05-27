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
        string $category,
        string $tag = ''
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->text = $text;
        $this->category = $category;
        $this->tag = $tag;
    }

    public function toArray(): array
    {
        if (!self::validateCategory($this->getCategory())) {
            throw new InvalidCategoryException('Cannot convert object to array, invalid Messenger Category');
        }

        return [
            'message_type' => $this->getSubType(),
            'text' => $this->getText(),
            'to' => $this->getTo(),
            'from' => $this->getFrom(),
            'channel' => $this->getChannel(),
            'client_ref' => $this->getClientRef(),
            'messenger' => $this->getMessengerObject()
        ];
    }
}
