<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Message\Callback;

use DateTime;
use UnexpectedValueException;
use Vonage\Client\Callback\Callback;

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

    /**
     * Receipt constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        //default value
        $data = array_merge(['client-ref' => null], $data);

        parent::__construct($data);
    }

    /**
     * @return int
     */
    public function getErrorCode(): int
    {
        return (int)$this->data['err-code'];
    }

    /**
     * @return string
     */
    public function getNetwork(): string
    {
        return (string)$this->data['network-code'];
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return (string)$this->data['messageId'];
    }

    /**
     * @return string
     */
    public function getReceiptFrom(): string
    {
        return (string)$this->data['msisdn'];
    }

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->getReceiptFrom();
    }

    /**
     * @return string
     */
    public function getReceiptTo(): string
    {
        return (string)$this->data['to'];
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->getReceiptTo();
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return (string)$this->data['status'];
    }

    /**
     * @return string
     */
    public function getPrice(): string
    {
        return (string)$this->data['price'];
    }

    /**
     * @return DateTime
     */
    public function getTimestamp(): DateTime
    {
        $date = DateTime::createFromFormat('ymdHi', $this->data['scts']);

        if ($date) {
            return $date;
        }

        throw new UnexpectedValueException('could not parse message timestamp');
    }

    /**
     * @return DateTime
     */
    public function getSent(): DateTime
    {
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $this->data['message-timestamp']);

        if ($date) {
            return $date;
        }

        throw new UnexpectedValueException('could not parse message timestamp');
    }

    /**
     * @return string|null
     */
    public function getClientRef(): ?string
    {
        return $this->data['client-ref'];
    }
}
