<?php

declare(strict_types=1);

namespace Vonage\Subaccount\SubaccountObjects;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class CreditTransfer implements ArrayHydrateInterface
{
    private string $creditTransferId;
    private float $amount;
    private string $from;
    private string $to;
    private string $reference;
    private string $createdAt;

    public function getCreditTransferId(): string
    {
        return $this->creditTransferId;
    }

    public function setCreditTransferId(string $creditTransferId): self
    {
        $this->creditTransferId = $creditTransferId;
        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function setFrom(string $from): self
    {
        $this->from = $from;
        return $this;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function setTo(string $to): self
    {
        $this->to = $to;
        return $this;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;
        return $this;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function fromArray(array $data): static
    {
        if (isset($data['credit_transfer_id'])) {
            $this->setCreditTransferId($data['credit_transfer_id']);
        }

        if (isset($data['amount'])) {
            $this->setAmount($data['amount']);
        }

        if (isset($data['from'])) {
            $this->setFrom($data['from']);
        }

        if (isset($data['to'])) {
            $this->setTo($data['to']);
        }

        if (isset($data['reference'])) {
            $this->setReference($data['reference']);
        }

        if (isset($data['created_at'])) {
            $this->setCreatedAt($data['created_at']);
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getCreditTransferId(),
            'amount' => $this->getAmount(),
            'from' => $this->getFrom(),
            'to' => $this->getTo(),
            'reference' => $this->getReference(),
            'created_at' => $this->getCreatedAt(),
        ];
    }
}
