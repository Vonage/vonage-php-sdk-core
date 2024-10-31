<?php

namespace Vonage\Messages\Channel\RCS;

use Vonage\Messages\MessageObjects\ImageObject;
use Vonage\Messages\Channel\BaseMessage;
use Vonage\Messages\MessageTraits\TtlTrait;

class RcsImage extends BaseMessage
{
    use TtlTrait;

    protected const RCS_TEXT_MIN_TTL = 300;
    protected const RCS_TEXT_MAX_TTL = 259200;

    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_IMAGE;
    protected string $channel = 'rcs';
    protected ImageObject $image;
    protected bool $validatesE164 = true;

    public function __construct(
        string $to,
        string $from,
        ImageObject $image
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->image = $image;
    }

    public function validatesE164(): bool
    {
        return $this->validatesE164;
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

    public function getImage(): ImageObject
    {
        return $this->image;
    }

    public function setImage(ImageObject $image): RcsImage
    {
        $this->image = $image;
        return $this;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();

        $returnArray['image'] = $this->getImage()->toArray();

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
