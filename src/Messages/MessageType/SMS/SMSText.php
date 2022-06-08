<?php

namespace Vonage\Messages\MessageType\SMS;

use Vonage\Messages\MessageTraits\TextTrait;
use Vonage\Messages\MessageType\BaseMessage;

class SMSText extends BaseMessage
{
    use TextTrait;

    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_TEXT;
    protected string $channel = 'sms';

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
        $returnArray = $this->baseMessageArrayOutput();
        $returnArray['text'] = $this->getText();

        return $returnArray;
    }
}
