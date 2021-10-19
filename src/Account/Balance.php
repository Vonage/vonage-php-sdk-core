<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Account;

use JsonSerializable;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Entity\JsonSerializableInterface;

class Balance implements
    JsonSerializable,
    JsonSerializableInterface,
    ArrayHydrateInterface
{
    /**
     * @var array
     */
    protected $data;

    public function __construct($balance, $autoReload)
    {
        $this->data['balance'] = $balance;
        $this->data['auto_reload'] = $autoReload;
    }

    public function getBalance()
    {
        return $this->data['balance'];
    }

    public function getAutoReload()
    {
        return $this->data['auto_reload'];
    }

    public function jsonSerialize(): array
    {
        return $this->data;
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
