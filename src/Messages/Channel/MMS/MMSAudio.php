<?php

namespace Vonage\Messages\Channel\MMS;

use Vonage\Messages\MessageObjects\AudioObject;
use Vonage\Messages\Channel\BaseMessage;

class MMSAudio extends BaseMessage
{
    protected string $channel = 'mms';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_AUDIO;

    public function __construct(
        string $to,
        string $from,
        protected AudioObject $audioObject
    ) {
        $this->to = $to;
        $this->from = $from;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $returnArray['audio'] = $this->audioObject->toArray();

        return $returnArray;
    }
}
