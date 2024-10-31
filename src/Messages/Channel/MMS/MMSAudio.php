<?php

namespace Vonage\Messages\Channel\MMS;

use Vonage\Messages\MessageObjects\AudioObject;
use Vonage\Messages\Channel\BaseMessage;
use Vonage\Messages\MessageTraits\TtlTrait;

class MMSAudio extends BaseMessage
{
    use TtlTrait;

    protected string $channel = 'mms';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_AUDIO;
    protected bool $validatesE164 = true;

    public function __construct(
        string $to,
        string $from,
        protected AudioObject $audioObject
    ) {
        $this->to = $to;
        $this->from = $from;
    }

    public function validatesE164(): bool
    {
        return $this->validatesE164;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $returnArray['audio'] = $this->audioObject->toArray();

        if (!is_null($this->ttl)) {
            $returnArray['ttl'] = $this->ttl;
        }

        return $returnArray;
    }
}
