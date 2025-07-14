<?php

namespace Vonage\Messages\Channel\MMS;

use Vonage\Messages\MessageObjects\ContentObject;
use Vonage\Messages\Channel\BaseMessage;
use Vonage\Messages\MessageTraits\TtlTrait;

class MMSContent extends BaseMessage
{
    use TtlTrait;

    protected string $channel = 'mms';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_CONTENT;
    protected bool $validatesE164 = true;

    public function __construct(
        string $to,
        string $from,
        protected ContentObject $content,
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
        $returnArray['content'] = $this->content->toArray();

        if (!is_null($this->ttl)) {
            $returnArray['ttl'] = $this->ttl;
        }

        return $returnArray;
    }
}
