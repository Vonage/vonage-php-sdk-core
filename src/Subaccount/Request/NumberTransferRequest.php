<?php

namespace Vonage\Subaccount\Request;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class NumberTransferRequest implements ArrayHydrateInterface
{
    public function __construct(
        protected string $apiKey,
        protected string $from,
        protected string $to,
        protected string $number,
        protected string $country
    ) {
    }

    public function setFrom(string $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function setTo(string $to): self
    {
        $this->to = $to;

        return $this;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function fromArray(array $data): self
    {
        $this->from = $data['from'] ?? '';
        $this->to = $data['to'] ?? '';
        $this->number = $data['number'] ?? '';
        $this->country = $data['country'] ?? '';

        return $this;
    }

    public function toArray(): array
    {
        return [
            'from' => $this->getFrom(),
            'to' => $this->getTo(),
            'number' => $this->getNumber(),
            'country' => $this->getCountry(),
        ];
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }
}
