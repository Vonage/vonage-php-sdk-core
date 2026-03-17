<?php

declare(strict_types=1);

namespace Vonage\Messages\Channel\RCS;

use Vonage\Messages\MessageObjects\VideoObject;
use Vonage\Messages\Channel\BaseMessage;
use Vonage\Messages\MessageTraits\TtlTrait;

class RcsVideo extends RcsBase
{
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_VIDEO;
    protected VideoObject $video;

    public function __construct(
        string $to,
        string $from,
        VideoObject $videoObject
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->video = $videoObject;
    }

    public function getVideo(): VideoObject
    {
        return $this->video;
    }

    public function setVideo(VideoObject $videoObject): RcsVideo
    {
        $this->video = $videoObject;
        return $this;
    }

    public function toArray(): array
    {
        $returnArray = parent::toArray();

        $returnArray['video'] = $this->getVideo()->toArray();

        return $returnArray;
    }
}
