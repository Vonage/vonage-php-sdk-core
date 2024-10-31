<?php

namespace Vonage\Messages\Channel\RCS;

use Vonage\Messages\MessageObjects\FileObject;
use Vonage\Messages\Channel\BaseMessage;
use Vonage\Messages\MessageTraits\TtlTrait;

class RcsCustom extends BaseMessage
{
    use TtlTrait;

    protected const RCS_TEXT_MIN_TTL = 300;
    protected const RCS_TEXT_MAX_TTL = 259200;

    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_FILE;
    protected string $channel = 'rcs';
    protected array $custom;
    protected bool $validatesE164 = true;

    public function __construct(
        string $to,
        string $from,
        array $custom
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->custom = $custom;
    }

    public function validatesE164(): bool
    {
        return $this->validatesE164;
    }

    public function getCustom(): array
    {
        return $this->custom;
    }

    public function setCustom(array $custom): RcsCustom
    {
        $this->custom = $custom;
        return $this;
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

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();

        $returnArray['custom'] = $this->getCustom();

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
