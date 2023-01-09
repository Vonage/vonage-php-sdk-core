<?php

declare(strict_types=1);

namespace Vonage\Voice\NCCO\Action;

use InvalidArgumentException;

class Pay implements ActionInterface
{
    protected const PERMITTED_VOICE_KEYS = ['language', 'style'];

    protected const PERMITTED_ERROR_KEYS = [
        'CardNumber' => [
            'InvalidCardType',
            'InvalidCardNumber',
            'Timeout'
        ],
        'ExpirationDate' => [
            'InvalidExpirationDate',
            'Timeout'
        ],
        'SecurityCode' => [
            'InvalidSecurityCode',
            'Timeout'
        ]
    ];

    /**
     * @var float
     */
    protected float $amount;

    /**
     * @var string
     */
    protected string $currency;

    /**
     * @var string
     */
    protected string $eventUrl;

    /**
     * @var array
     */
    protected array $prompts;

    /**
     * @var array
     */
    protected array $voice;

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): void
    {
        $this->currency = $currency;
    }

    public function getEventUrl(): ?string
    {
        return $this->eventUrl;
    }

    public function setEventUrl(string $eventUrl): void
    {
        $this->eventUrl = $eventUrl;
    }

    /**
     * @return array
     */
    public function getPrompts(): array
    {
        return $this->prompts;
    }

    public function setPrompts(array $prompts): void
    {
        if (!array_key_exists('type', $prompts)) {
            throw new InvalidArgumentException('type is required when setting a text prompt.');
        }

        if (!array_key_exists($prompts['type'], self::PERMITTED_ERROR_KEYS)) {
            throw new InvalidArgumentException('invalid prompt type.');
        }

        if (!array_key_exists('text', $prompts)) {
            throw new InvalidArgumentException('text is required when setting error text prompts..');
        }

        if (!array_key_exists('errors', $prompts)) {
            throw new InvalidArgumentException('error settings are required when setting am error text prompt.');
        }

        foreach ($prompts['errors'] as $errorPromptKey => $errorPromptData) {
            if (!array_key_exists('text', $errorPromptData)) {
                throw new InvalidArgumentException('text is required when setting error text prompts.');
            }

            $permittedErrors = self::PERMITTED_ERROR_KEYS[$prompts['type']];

            if (!in_array($errorPromptKey, $permittedErrors, true)) {
                throw new InvalidArgumentException('incorrect error type for prompt.');
            }
        }

        $this->prompts = $prompts;
    }

    /**
     * @return ?array
     */
    public function getVoice(): array
    {
        return $this->voice;
    }

    public function setVoice(array $settings): void
    {
        foreach (array_keys($settings) as $settingKey) {
            if (!in_array($settingKey, self::PERMITTED_VOICE_KEYS, true)) {
                throw new InvalidArgumentException($settingKey . ' did not fall under permitted voice settings');
            }
        }

        $this->voice = $settings;
    }

    public function toNCCOArray(): array
    {
        $data = [
            'action' => 'pay',
            'amount' => $this->getAmount()
        ];

        if (isset($this->currency)) {
            $data['currency'] = $this->getCurrency();
        }

        if (isset($this->eventUrl)) {
            $data['eventUrl'] = $this->getEventUrl();
        }

        if (isset($this->prompts)) {
            $data['prompts'] = $this->getPrompts();
        }

        if (isset($this->voice)) {
            $data['voice'] = $this->getVoice();
        }

        return $data;
    }

    public function jsonSerialize(): array
    {
        return $this->toNCCOArray();
    }

    public static function factory(array $data): Pay
    {
        $pay = new self();

        if (array_key_exists('amount', $data)) {
            $pay->setAmount($data['amount']);
        } else {
            throw new InvalidArgumentException('Amount is required for this action.');
        }

        if (array_key_exists('currency', $data)) {
            $pay->setCurrency($data['currency']);
        }

        if (array_key_exists('eventUrl', $data)) {
            $pay->setEventUrl($data['eventUrl']);
        }

        if (array_key_exists('prompts', $data)) {
            $pay->setPrompts($data['prompts']);
        }

        if (array_key_exists('voice', $data)) {
            $pay->setVoice($data['voice']);
        }

        return $pay;
    }
}
