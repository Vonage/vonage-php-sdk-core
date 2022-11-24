<?php

namespace Vonage\Messages\Channel\Messenger;

use Vonage\Messages\MessageObjects\AudioObject;
use Vonage\Messages\Channel\BaseMessage;

class MessengerAudio extends BaseMessage
{
    use MessengerObjectTrait;

    protected string $channel = 'messenger';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_AUDIO;

    public function __construct(
        string $to,
        string $from,
        protected AudioObject $audioObject,
        ?string $category = null,
        ?string $tag = null
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->category = $category;
        $this->tag = $tag;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $returnArray['audio'] = $this->audioObject->toArray();

        if ($this->requiresMessengerObject()) {
            $returnArray['messenger'] = $this->getMessengerObject();
        }

        return $returnArray;
    }
}
