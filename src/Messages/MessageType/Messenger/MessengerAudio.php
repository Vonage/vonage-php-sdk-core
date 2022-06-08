<?php

namespace Vonage\Messages\MessageType\Messenger;

use Vonage\Messages\MessageObjects\AudioObject;
use Vonage\Messages\MessageType\BaseMessage;

class MessengerAudio extends BaseMessage
{
    use MessengerObjectTrait;

    protected string $channel = 'messenger';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_AUDIO;
    protected AudioObject $audioObject;

    public function __construct(
        string $to,
        string $from,
        AudioObject $audioObject,
        ?string $category = null,
        ?string $tag = null
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->audioObject = $audioObject;
        $this->category = $category;
        $this->tag = $tag;
    }

    public function toArray(): array
    {
        $returnArray = $this->baseMessageArrayOutput();
        $returnArray['audio'] = $this->audioObject->toArray();

        if ($this->requiresMessengerObject()) {
            $returnArray['messenger'] = $this->getMessengerObject();
        }

        return $returnArray;
    }
}
