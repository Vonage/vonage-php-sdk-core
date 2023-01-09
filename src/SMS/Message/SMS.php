<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\SMS\Message;

class SMS extends OutboundMessage
{
    public const GSM_7_PATTERN = '/\A[\n\f\r !\"\#$%&\'()*+,-.\/0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\\\\^_abcdefghijklmnopqrstuvwxyz{\|}~ ¡£¤¥§¿ÄÅÆÇÉÑÖØÜßàäåæèéìñòöøùüΓΔΘΛΞΠΣΦΨΩ€]*\z/m';

    /**
     * @var string
     */
    protected $contentId;

    /**
     * @var string
     */
    protected $entityId;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var string
     */
    protected $type = 'text';

    public function __construct(string $to, string $from, string $message, string $type = 'text')
    {
        parent::__construct($to, $from);

        $this->message = $message;
        $this->setType($type);
    }

    public function encodingError(): bool
    {
        if ($this->getType() === 'unicode' && $this->isGsm7()) {
            return true;
        }

        if ($this->getType() === 'text' && ! $this->isGsm7()) {
            return true;
        }

        return false;
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
