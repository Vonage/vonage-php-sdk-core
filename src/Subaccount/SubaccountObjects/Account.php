<?php

declare(strict_types=1);

namespace Vonage\Subaccount\SubaccountObjects;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class Account implements ArrayHydrateInterface
{
    protected string $apiKey;
    protected string $name;
    protected string $primaryAccountApiKey;
    protected bool $usePrimaryAccountBalance;
    protected string $createdAt;
    protected bool $suspended;
    protected float $balance;
    protected float $creditLimit;

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey($apiKey): static
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrimaryAccountApiKey(): string
    {
        return $this->primaryAccountApiKey;
    }

    public function setPrimaryAccountApiKey($primaryAccountApiKey): static
    {
        $this->primaryAccountApiKey = $primaryAccountApiKey;

        return $this;
    }

    public function getUsePrimaryAccountBalance(): bool
    {
        return $this->usePrimaryAccountBalance;
    }

    public function setUsePrimaryAccountBalance($usePrimaryAccountBalance): static
    {
        $this->usePrimaryAccountBalance = $usePrimaryAccountBalance;

        return $this;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getSuspended(): bool
    {
        return $this->suspended;
    }

    public function setSuspended($suspended): static
    {
        $this->suspended = $suspended;

        return $this;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function setBalance($balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    public function getCreditLimit(): float
    {
        return $this->creditLimit;
    }

    public function setCreditLimit($creditLimit): static
    {
        $this->creditLimit = $creditLimit;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'api_key' => $this->getApiKey(),
            'name' => $this->getName(),
            'primary_account_api_key' => $this->getPrimaryAccountApiKey(),
            'use_primary_account_balance' => $this->getUsePrimaryAccountBalance(),
            'created_at' => $this->getCreatedAt(),
            'suspended' => $this->getSuspended(),
            'balance' => $this->getBalance(),
            'credit_limit' => $this->getCreditLimit()
        ];
    }

    public function fromArray(array $data): static
    {
        $this->apiKey = $data['api_key'];
        $this->name = $data['name'];
        $this->primaryAccountApiKey = $data['primary_account_api_key'];
        $this->usePrimaryAccountBalance = $data['use_primary_account_balance'];
        $this->createdAt = $data['created_at'];
        $this->suspended = $data['suspended'];
        $this->balance = $data['balance'];
        $this->creditLimit = $data['credit_limit'];

        return $this;
    }
}
