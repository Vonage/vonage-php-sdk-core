<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\SMS\Message;

class SMS extends OutboundMessage
{
    public const GSM_7_CHARSET = "\n\f\r !\"\#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_abcdefghijklmnopqrstuvwxyz{|}~ ¡£¤¥§¿ÄÅÆÇÉÑÖØÜßàäåæèéìñòöøùüΓΔΘΛΞΠΣΦΨΩ€";

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

    public static function isGsm7(string $message): bool
    {
        $fullPattern = "/\A[" . preg_quote(self::GSM_7_CHARSET, '/') . "]*\z/u";
        return (bool)preg_match($fullPattern, $message);
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

    public function getWarningMessage(): ?string
    {
        if ($this->getType() === 'text' && ! self::isGsm7($this->getMessage())) {
            $this->setWarningMessage("You are sending a message as `text` which contains non-GSM7 
            characters. This could result in encoding problems with the target device - See 
            https://developer.vonage.com/messaging/sms for details, or email support@vonage.com if you have any 
            questions.");
        }

        return $this->warningMessage;
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
