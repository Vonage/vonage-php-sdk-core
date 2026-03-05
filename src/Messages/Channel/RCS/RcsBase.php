<?php

namespace Vonage\Messages\Channel\RCS;

use Vonage\Messages\Channel\BaseMessage;
use Vonage\Messages\MessageTraits\TtlTrait;

enum RCSCategory: string
{
    case Authentication = 'authentication';
    case Transaction = 'transaction';
    case Promotion = 'promotion';
    case ServiceRequest = 'service-request';
    case Acknowledgement = 'acknowledgement';
}

abstract class RcsBase extends BaseMessage
{
    use TtlTrait;

    protected const RCS_TEXT_MIN_TTL = 300;
    protected const RCS_TEXT_MAX_TTL = 259200;

    protected string $channel = 'rcs';
    protected bool $validatesE164 = true;
    protected ?bool $trustedRecipient = null;
    protected ?RCSCategory $category = null;

    public function __construct(
        string $to,
        string $from,
    ) {
        $this->to = $to;
        $this->from = $from;
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

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();

        if ($this->getTtl()) {
            $returnArray['ttl'] = $this->getTtl();
        }

        if ($this->getCategory() !== null) {
            $returnArray['rcs']['category'] = $this->getCategory()->value;
        }

        if ($this->getTrustedRecipient() !== null) {
            $returnArray['trusted_recipient'] = $this->getTrustedRecipient();
        }

        return $returnArray;
    }

    public function getTrustedRecipient(): ?bool
    {
        return $this->trustedRecipient;
    }

    public function setTrustedRecipient(bool $trustedRecipient): void
    {
        $this->trustedRecipient = $trustedRecipient;
    }

    public function setCategory(?RCSCategory $category): void
    {
        $this->category = $category;
    }

    public function getCategory(): ?RCSCategory
    {
        return $this->category;
    }
}
