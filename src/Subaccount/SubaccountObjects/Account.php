<?php

declare(strict_types=1);

namespace Vonage\Subaccount\SubaccountObjects;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class Account implements ArrayHydrateInterface
{
    protected ?string $apiKey = null;
    protected ?string $name = null;
    protected ?string $primaryAccountApiKey = null;
    protected ?bool $usePrimaryAccountBalance = null;
    protected ?string $createdAt = null;
    protected ?bool $suspended = null;
    protected ?float $balance = null;
    protected ?float $creditLimit = null;
    protected ?string $secret = null;

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey): static
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrimaryAccountApiKey(): ?string
    {
        return $this->primaryAccountApiKey;
    }

    public function setPrimaryAccountApiKey(?string $primaryAccountApiKey): static
    {
        $this->primaryAccountApiKey = $primaryAccountApiKey;

        return $this;
    }

    public function getUsePrimaryAccountBalance(): ?bool
    {
        return $this->usePrimaryAccountBalance;
    }

    public function setUsePrimaryAccountBalance(?bool $usePrimaryAccountBalance): static
    {
        $this->usePrimaryAccountBalance = $usePrimaryAccountBalance;

        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?string $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getSuspended(): ?bool
    {
        return $this->suspended;
    }

    public function setSuspended(?bool $suspended): static
    {
        $this->suspended = $suspended;

        return $this;
    }

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(?float $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    public function getCreditLimit(): ?float
    {
        return $this->creditLimit;
    }

    public function setCreditLimit(?float $creditLimit): static
    {
        $this->creditLimit = $creditLimit;

        return $this;
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->apiKey !== null) {
            $data['api_key'] = $this->getApiKey();
        }

        if ($this->name !== null) {
            $data['name'] = $this->getName();
        }

        if ($this->primaryAccountApiKey !== null) {
            $data['primary_account_api_key'] = $this->getPrimaryAccountApiKey();
        }

        if ($this->usePrimaryAccountBalance !== null) {
            $data['use_primary_account_balance'] = $this->getUsePrimaryAccountBalance();
        }

        if ($this->createdAt !== null) {
            $data['created_at'] = $this->getCreatedAt();
        }

        if ($this->suspended !== null) {
            $data['suspended'] = $this->getSuspended();
        }

        if ($this->balance !== null) {
            $data['balance'] = $this->getBalance();
        }

        if ($this->creditLimit !== null) {
            $data['credit_limit'] = $this->getCreditLimit();
        }

        if ($this->secret !== null) {
            $data['secret'] = $this->getSecret();
        }

        return $data;
    }

    public function fromArray(array $data): static
    {
        if (isset($data['api_key'])) {
            $this->apiKey = $data['api_key'];
        }

        if (isset($data['name'])) {
            $this->name = $data['name'];
        }

        if (isset($data['primary_account_api_key'])) {
            $this->primaryAccountApiKey = $data['primary_account_api_key'];
        }

        if (isset($data['use_primary_account_balance'])) {
            $this->usePrimaryAccountBalance = $data['use_primary_account_balance'];
        }

        if (isset($data['created_at'])) {
            $this->createdAt = $data['created_at'];
        }

        if (isset($data['suspended'])) {
            $this->suspended = $data['suspended'];
        }

        if (isset($data['balance'])) {
            $this->balance = $data['balance'];
        }

        if (isset($data['credit_limit'])) {
            $this->creditLimit = $data['credit_limit'];
        }

        if (isset($data['secret'])) {
            $this->secret = $data['secret'];
        }

        return $this;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): void
    {
        $this->secret = $secret;
    }
}
