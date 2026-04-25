<?php

declare(strict_types=1);

namespace Vonage\Verify;

use InvalidArgumentException;

use function strlen;

class StartVerification
{
    public const PIN_LENGTH_4 = 4;
    public const PIN_LENGTH_6 = 6;

    public const WORKFLOW_SMS_TTS_TSS = 1;
    public const WORKFLOW_SMS_SMS_TSS = 2;
    public const WORKFLOW_TTS_TSS = 3;
    public const WORKFLOW_SMS_SMS = 4;
    public const WORKFLOW_SMS_TTS = 5;
    public const WORKFLOW_SMS = 6;
    public const WORKFLOW_TTS = 7;

    protected string $country = '';
    protected string $senderId = 'VONAGE';
    protected int $codeLength = 4;
    protected string $locale = '';
    protected int $pinExpiry = 300;
    protected int $nextEventWait = 300;
    protected int $workflowId;

    public function __construct(
        protected string $number,
        protected string $brand,
        int $workflowId = self::WORKFLOW_SMS_TTS_TSS
    ) {
        $this->setWorkflowId($workflowId);
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        if (strlen($country) !== 2) {
            throw new InvalidArgumentException('Country must be in two character format');
        }

        $this->country = $country;

        return $this;
    }

    public function getSenderId(): string
    {
        return $this->senderId;
    }

    public function setSenderId(string $senderId): static
    {
        $this->senderId = $senderId;

        return $this;
    }

    public function getCodeLength(): int
    {
        return $this->codeLength;
    }

    public function setCodeLength(int $codeLength): static
    {
        if ($codeLength !== self::PIN_LENGTH_4 && $codeLength !== self::PIN_LENGTH_6) {
            throw new InvalidArgumentException(
                sprintf('Pin length must be either %d or %d digits', self::PIN_LENGTH_4, self::PIN_LENGTH_6)
            );
        }

        $this->codeLength = $codeLength;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getPinExpiry(): int
    {
        return $this->pinExpiry;
    }

    public function setPinExpiry(int $pinExpiry): static
    {
        if ($pinExpiry < 60 || $pinExpiry > 3600) {
            throw new InvalidArgumentException('Pin expiration must be between 60 and 3600 seconds');
        }

        $this->pinExpiry = $pinExpiry;

        return $this;
    }

    public function getNextEventWait(): int
    {
        return $this->nextEventWait;
    }

    public function setNextEventWait(int $nextEventWait): static
    {
        if ($nextEventWait < 60 || $nextEventWait > 3600) {
            throw new InvalidArgumentException('Next Event time must be between 60 and 900 seconds');
        }

        $this->nextEventWait = $nextEventWait;

        return $this;
    }

    public function getWorkflowId(): int
    {
        return $this->workflowId;
    }

    public function setWorkflowId(int $workflowId): static
    {
        if ($workflowId < 1 || $workflowId > 7) {
            throw new InvalidArgumentException('Workflow ID must be from 1 to 7');
        }

        $this->workflowId = $workflowId;

        return $this;
    }

    public function toArray(): array
    {
        $data = [
            'number' => $this->number,
            'brand' => $this->brand,
            'sender_id' => $this->senderId,
            'code_length' => $this->codeLength,
            'pin_expiry' => $this->pinExpiry,
            'next_event_wait' => $this->nextEventWait,
            'workflow_id' => $this->workflowId,
        ];

        if ($this->country !== '') {
            $data['country'] = $this->country;
        }

        if ($this->locale !== '') {
            $data['lg'] = $this->locale;
        }

        return $data;
    }
}
