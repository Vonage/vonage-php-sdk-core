<?php

namespace Vonage\Messages\Channel\Viber;

use Vonage\Messages\MessageObjects\VideoObject;
use Vonage\Messages\MessageTraits\TextTrait;
use Vonage\Messages\Channel\BaseMessage;

class ViberVideo extends BaseMessage
{
    use TextTrait;
    use ViberServiceObjectTrait;

    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_VIDEO;
    protected string $channel = 'viber_service';

    public function __construct(
        string $to,
        string $from,
        protected VideoObject $videoObject
    ) {
        $this->to = $to;
        $this->from = $from;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $returnArray['video'] = $this->videoObject->toArray();

        return $returnArray;
    }
}
