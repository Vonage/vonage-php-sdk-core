<?php

namespace Vonage\Messages\Channel\RCS;

use Vonage\Messages\MessageTraits\SuggestionsTrait;
use Vonage\Messages\MessageTraits\TextTrait;
use Vonage\Messages\Channel\BaseMessage;

class RcsText extends RcsBase
{
    use SuggestionsTrait;
    use TextTrait;

    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_TEXT;

    public function __construct(
        string $to,
        string $from,
        string $message
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->text = $message;
    }

    public function toArray(): array
    {
        $returnArray = parent::toArray();

        $returnArray['text'] = $this->getText();

        if (isset($this->suggestions) && count($this->suggestions) > 0) {
            $returnArray['suggestions'] = $this->suggestions->toArray();
        }

        return $returnArray;
    }
}
