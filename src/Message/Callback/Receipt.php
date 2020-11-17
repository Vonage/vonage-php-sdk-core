<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Message\Callback;

use DateTime;
use UnexpectedValueException;
use Vonage\Client\Callback\Callback;

use function array_merge;

class Receipt extends Callback
{
    protected $expected = [
        'err-code',
        'message-timestamp',
        'msisdn',
        'network-code',
        'price',
        'scts',
        'status',
        //'timestamp',
        'to'
    ];

    public function __construct(array $data)
    {
        //default value
        $data = array_merge(['client-ref' => null], $data);

        parent::__construct($data);
    }

    public function getErrorCode(): int
    {
        return (int)$this->data['err-code'];
    }

    public function getNetwork(): string
    {
        return (string)$this->data['network-code'];
    }

    public function getId(): string
    {
        return (string)$this->data['messageId'];
    }

    public function getReceiptFrom(): string
    {
        return (string)$this->data['msisdn'];
    }

    public function getTo(): string
    {
        return $this->getReceiptFrom();
    }

    public function getReceiptTo(): string
    {
        return (string)$this->data['to'];
    }

    public function getFrom(): string
    {
        return $this->getReceiptTo();
    }

    public function getStatus(): string
    {
        return (string)$this->data['status'];
    }

    public function getPrice(): string
    {
        return (string)$this->data['price'];
    }

    public function getTimestamp(): DateTime
    {
        $date = DateTime::createFromFormat('ymdHi', $this->data['scts']);

        if ($date) {
            return $date;
        }

        throw new UnexpectedValueException('could not parse message timestamp');
    }

    public function getSent(): DateTime
    {
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $this->data['message-timestamp']);

        if ($date) {
            return $date;
        }

        throw new UnexpectedValueException('could not parse message timestamp');
    }

    public function getClientRef(): ?string
    {
        return $this->data['client-ref'];
    }
}
