<?php

namespace Vonage\Messages\Channel\MMS;

use Vonage\Messages\MessageObjects\AudioObject;
use Vonage\Messages\MessageObjects\VideoObject;
use Vonage\Messages\Channel\BaseMessage;

class MMSVideo extends BaseMessage
{
    protected string $channel = 'mms';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_VIDEO;
    protected VideoObject $videoObject;

    public function __construct(
        string $to,
        string $from,
        VideoObject $videoObject
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->videoObject = $videoObject;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $returnArray['video'] = $this->videoObject->toArray();

        return $returnArray;
    }
}
