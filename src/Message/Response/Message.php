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

        parent::__construct($data);

        if ($receipt !== null && $receipt->getId() !== $this->getId()) {
            throw new UnexpectedValueException('receipt id must match message id');
        }

        $this->receipt = $receipt;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return (int)$this->data['status'];
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return (string)$this->data['message-id'];
    }

    /**
     * @return string
     */
    public function getTo(): string
    {
        return (string)$this->data['to'];
    }

    /**
     * @return string
     */
    public function getBalance(): string
    {
        return (string)$this->data['remaining-balance'];
    }

    /**
     * @return string
     */
    public function getPrice(): string
    {
        return (string)$this->data['message-price'];
    }

    /**
     * @return string
     */
    public function getNetwork(): string
    {
        return (string)$this->data['network'];
    }

    /**
     * @return string
     */
    public function getClientRef(): string
    {
        return (string)$this->data['client-ref'];
    }

    /**
     * @return Receipt|null
     */
    public function getReceipt(): ?Receipt
    {
        return $this->receipt;
    }

    /**
     * @return bool
     */
    public function hasReceipt(): bool
    {
        return $this->receipt instanceof Receipt;
    }
}
