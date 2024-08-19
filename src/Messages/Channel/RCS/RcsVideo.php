<?php

namespace Vonage\Messages\Channel\RCS;

use Vonage\Messages\MessageObjects\VideoObject;
use Vonage\Messages\Channel\BaseMessage;
use Vonage\Messages\MessageTraits\TtlTrait;

class RcsVideo extends BaseMessage
{
    use TtlTrait;

    protected const RCS_TEXT_MIN_TTL = 300;
    protected const RCS_TEXT_MAX_TTL = 259200;

    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_VIDEO;
    protected string $channel = 'rcs';
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

    public function setTtl(?int $ttl): void
    {
        $range = [
            'options' => [
                'min_range' => self::RCS_TEXT_MIN_TTL,
                'max_range' => self::RCS_TEXT_MAX_TTL
            ]
        ];

        if (!filter_var($ttl, FILTER_VALIDATE_INT, $range)) {
            throw new RcsInvalidTtlException('Timeout ' . $ttl . ' is not valid');
        }

        $this->ttl = $ttl;
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
        $returnArray = $this->getBaseMessageUniversalOutputArray();

        $returnArray['video'] = $this->getVideo()->toArray();

        if ($this->getClientRef()) {
            $returnArray['client_ref'] = $this->getClientRef();
        }

        if ($this->getWebhookUrl()) {
            $returnArray['webhook_url'] = $this->getWebhookUrl();
        }

        if ($this->getTtl()) {
            $returnArray['ttl'] = $this->getTtl();
        }

        return $returnArray;
    }
}
