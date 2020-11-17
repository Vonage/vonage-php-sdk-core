<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Message\Response;

use UnexpectedValueException;
use Vonage\Client\Response\Response;
use Vonage\Message\Callback\Receipt;

use function array_merge;

class Message extends Response
{
    /**
     * @var Receipt
     */
    protected $receipt;

    public function __construct(array $data, Receipt $receipt = null)
    {
        $this->expected = [
            'status',
            'message-id',
            'to',
            'message-price',
            'network'
        ];

        //default value
        $data = array_merge(['client-ref' => null, 'remaining-balance' => null], $data);

        $return = parent::__construct($data);

        //validate receipt
        if (!$receipt) {
            return $return;
        }

        if ($receipt->getId() !== $this->getId()) {
            throw new UnexpectedValueException('receipt id must match message id');
        }

        $this->receipt = $receipt;

        return $receipt;
    }

    public function getStatus(): int
    {
        return (int)$this->data['status'];
    }

    public function getId(): string
    {
        return (string)$this->data['message-id'];
    }

    public function getTo(): string
    {
        return (string)$this->data['to'];
    }

    public function getBalance(): string
    {
        return (string)$this->data['remaining-balance'];
    }

    public function getPrice(): string
    {
        return (string)$this->data['message-price'];
    }

    public function getNetwork(): string
    {
        return (string)$this->data['network'];
    }

    public function getClientRef(): string
    {
        return (string)$this->data['client-ref'];
    }

    public function getReceipt(): ?Receipt
    {
        return $this->receipt;
    }

    public function hasReceipt(): bool
    {
        return $this->receipt instanceof Receipt;
    }
}
