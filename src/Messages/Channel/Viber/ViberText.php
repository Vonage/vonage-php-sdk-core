<?php

namespace Vonage\Messages\Channel\Viber;

use Vonage\Messages\MessageTraits\TextTrait;
use Vonage\Messages\Channel\BaseMessage;

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
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $returnArray['text'] = $this->getText();

        if ($this->requiresViberServiceObject()) {
            $returnArray['viber_service']['category'] = $this->getCategory();
            $returnArray['viber_service']['ttl'] = $this->getTtl();
            $returnArray['viber_service']['type'] = $this->getType();
        }

        return array_filter($returnArray);
    }
}
