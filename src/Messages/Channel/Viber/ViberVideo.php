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
    protected string $thumbUrl = "";

    public function __construct(
        string $to,
        string $from,
        string $thumbUrl,
        protected VideoObject $videoObject
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->thumbUrl = $thumbUrl;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $videoArray = $this->videoObject->toArray();
        $videoArray['thumb_url'] = $this->thumbUrl;
        $returnArray['video'] = $videoArray;

        return $returnArray;
    }
}
