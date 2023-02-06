<?php

declare(strict_types=1);

namespace Vonage\SMS\Message;

class SMS extends OutboundMessage
{
    public const GSM_7_PATTERN = '/\A[\n\f\r !\"\#$%&\'()*+,-.\/0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\\\\^_abcdefghijklmnopqrstuvwxyz{\|}~ ¡£¤¥§¿ÄÅÆÇÉÑÖØÜßàäåæèéìñòöøùüΓΔΘΛΞΠΣΦΨΩ€]*\z/m';

    protected ?string $contentId;

    protected ?string $entityId;

    /**
     * @var string
     */
    protected string $type = 'text';

    public function __construct(string $to, string $from, protected string $message, string $type = 'text')
    {
        parent::__construct($to, $from);
        $this->setType($type);
    }

    public function isGsm7(): bool
    {
        return (bool)preg_match(self::GSM_7_PATTERN, $this->getMessage());
    }

    public function getContentId(): string
    {
        return $this->contentId;
    }

    public function getEntityId(): string
    {
        return $this->entityId;
    }

    public function setContentId(string $id): self
    {
        $this->contentId = $id;
        return $this;
    }

    public function setEntityId(string $id): self
    {
        $this->entityId = $id;
        return $this;
    }

    public function getErrorMessage(): ?string
    {
        if ($this->getType() === 'unicode' && $this->isGsm7()) {
            $this->setErrorMessage("You are sending a message as `unicode` when it could be `text` or a
            `text` type with unicode-only characters. This could result in increased billing - 
            See https://developer.vonage.com/messaging/sms for details, or email support@vonage.com if you have any 
            questions.");
        }

        if ($this->getType() === 'text' && ! $this->isGsm7()) {
            $this->setErrorMessage("You are sending a message as `text` when contains unicode only 
            characters. This could result in encoding problems with the target device - See 
            https://developer.vonage.com/messaging/sms for details, or email support@vonage.com if you have any 
            questions.");
        }

        return $this->errorMessage;
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        $data = ['text' => $this->getMessage()];
        if (!empty($this->entityId)) {
            $data['entity-id'] = $this->entityId;
        }

        if (!empty($this->contentId)) {
            $data['content-id'] = $this->contentId;
        }

        $data = $this->appendUniversalOptions($data);

        return $data;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function enableDLT(string $entityId, string $templateId): self
    {
        $this->entityId = $entityId;
        $this->contentId = $templateId;

        return $this;
    }
}
