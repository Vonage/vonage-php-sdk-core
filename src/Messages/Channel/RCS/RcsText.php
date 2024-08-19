<?php

namespace Vonage\Messages\Channel\RCS;

use Vonage\Messages\MessageTraits\TextTrait;
use Vonage\Messages\Channel\BaseMessage;
use Vonage\Messages\MessageTraits\TtlTrait;

class RcsText extends BaseMessage
{
    use TextTrait;
    use TtlTrait;

    protected const RCS_TEXT_MIN_TTL = 300;
    protected const RCS_TEXT_MAX_TTL = 259200;

    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_TEXT;
    protected string $channel = 'rcs';

    public function __construct(
        string $to,
        string $from,
        string $message
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->text = $message;
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
        $returnArray['text'] = $this->getText();

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
