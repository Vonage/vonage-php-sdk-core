<?php

namespace Vonage\Messages\Channel\Messenger;

use Vonage\Messages\MessageObjects\VideoObject;
use Vonage\Messages\Channel\BaseMessage;

class MessengerVideo extends BaseMessage
{
    use MessengerObjectTrait;

    protected string $channel = 'messenger';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_VIDEO;

    public function __construct(
        string $to,
        string $from,
        protected VideoObject $videoObject,
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
        $returnArray['video'] = $this->videoObject->toArray();

        if ($this->requiresMessengerObject()) {
            $returnArray['messenger'] = $this->getMessengerObject();
        }

        return $returnArray;
    }
}