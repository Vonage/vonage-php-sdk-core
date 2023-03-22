<?php

namespace Vonage\Messages\Channel\Viber;

use Vonage\Messages\Channel\Viber\MessageObjects\ViberActionObject;
use Vonage\Messages\MessageObjects\ImageObject;
use Vonage\Messages\Channel\BaseMessage;

class ViberImage extends BaseMessage
{
    use ViberServiceObjectTrait;

    protected string $channel = 'viber_service';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_IMAGE;

    public function __construct(
        string $to,
        string $from,
        protected ImageObject $image,
        ?string $category = null,
        ?int $ttl = null,
        ?string $type = null,
        ?ViberActionObject $viberActionObject = null
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->category = $category;
        $this->ttl = $ttl;
        $this->type = $type;
        $this->action = $viberActionObject;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $returnArray['image'] = $this->image->toArray();

        if ($this->requiresViberServiceObject()) {
            $this->getCategory() ? $returnArray['viber_service']['category'] = $this->getCategory(): null;
            $this->getTtl() ? $returnArray['viber_service']['ttl'] = $this->getTtl(): null;
            $this->getType() ? $returnArray['viber_service']['type'] = $this->getType(): null;
            $this->getAction() ? $returnArray['viber_service']['action'] = $this->getAction()->toArray(): null;
        }

        return array_filter($returnArray);
    }
}
