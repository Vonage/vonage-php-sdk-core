<?php

declare(strict_types=1);

namespace Vonage\Account;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class Balance implements
    ArrayHydrateInterface
{
    protected array $data;

    public function __construct(float $balance, bool $autoReload)
    {
        $this->data['balance'] = $balance;
        $this->data['auto_reload'] = $autoReload;
    }

    public function getBalance(): float
    {
        return $this->data['balance'];
    }

    public function getAutoReload(): bool
    {
        return $this->data['auto_reload'];
    }

    public function fromArray(array $data): void
    {
        $this->data = [
            'balance' => $data['value'],
            'auto_reload' => $data['autoReload']
        ];
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
