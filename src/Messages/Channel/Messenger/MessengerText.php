<?php

namespace Vonage\Messages\Channel\Messenger;

use Vonage\Messages\MessageTraits\TextTrait;
use Vonage\Messages\Channel\BaseMessage;

class MessengerText extends BaseMessage
{
    use TextTrait;
    use MessengerObjectTrait;

    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_TEXT;
    protected string $channel = 'messenger';
    protected bool $validatesE164 = false;

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

    public function validatesE164(): bool
    {
        return $this->validatesE164;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $returnArray['text'] = $this->getText();

        if ($this->requiresMessengerObject()) {
            $returnArray['messenger'] = $this->getMessengerObject();
        }

        return $returnArray;
    }
}
