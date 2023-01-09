<?php

namespace Vonage\Messages\Channel\Messenger;

use Vonage\Messages\MessageObjects\ImageObject;
use Vonage\Messages\Channel\BaseMessage;

class MessengerImage extends BaseMessage
{
    use MessengerObjectTrait;

    protected string $channel = 'messenger';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_IMAGE;

    public function __construct(
        string $to,
        string $from,
        protected ImageObject $image,
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
        $returnArray['image'] = $this->image->toArray();

        if ($this->requiresMessengerObject()) {
            $returnArray['messenger'] = $this->getMessengerObject();
        }

        return $returnArray;
    }
}
