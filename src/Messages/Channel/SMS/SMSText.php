<?php

namespace Vonage\Messages\Channel\SMS;

use Vonage\Messages\MessageTraits\TextTrait;
use Vonage\Messages\Channel\BaseMessage;

class SMSText extends BaseMessage
{
    use TextTrait;

    protected array $permittedEncodingTypes = [
        'text',
        'unicode',
        'auto'
    ];

    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_TEXT;
    protected string $channel = 'sms';
    protected ?int $ttl = null;
    protected ?string $encodingType = null;
    protected ?string $contentId = null;
    protected ?string $entityId = null;
    protected bool $validatesE164 = true;

    public function __construct(
        string $to,
        string $from,
        string $message
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->text = $message;
    }

    public function validatesE164(): bool
    {
        return $this->validatesE164;
    }

    public function getEncodingType(): ?string
    {
        return $this->encodingType;
    }

    public function setEncodingType(?string $encodingType): void
    {
        if (! in_array($encodingType, $this->permittedEncodingTypes, true)) {
            throw new \InvalidArgumentException($encodingType . ' is not a valid encoding type');
        }

        $this->encodingType = $encodingType;
    }

    public function getContentId(): ?string
    {
        return $this->contentId;
    }

    public function setContentId(?string $contentId): void
    {
        $this->contentId = $contentId;
    }

    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    public function setEntityId(?string $entityId): void
    {
        $this->entityId = $entityId;
    }

    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    public function setTtl(?int $ttl): void
    {
        $this->ttl = $ttl;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $returnArray['text'] = $this->getText();

        if ($this->getEncodingType()) {
            $returnArray['sms']['encoding_type'] = $this->getEncodingType();
        }

        if ($this->getContentId()) {
            $returnArray['sms']['content_id'] = $this->getContentId();
        }

        if ($this->getEntityId()) {
            $returnArray['sms']['entity_id'] = $this->getEntityId();
        }

        if ($this->getTtl()) {
            $returnArray['ttl'] = $this->getTtl();
        }

        return $returnArray;
    }
}
