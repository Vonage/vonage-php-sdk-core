<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Message\Callback;

use Vonage\Client\Callback\Callback;

class Receipt extends Callback
{
    protected $expected = array(
        'err-code',
        'message-timestamp',
        'msisdn',
        'network-code',
        'price',
        'scts',
        'status',
        //'timestamp',
        'to'
    );

    public function __construct(array $data)
    {
        //default value
        $data = array_merge(array('client-ref' => null), $data);

        parent::__construct($data);
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return (int) $this->data['err-code'];
    }

    /**
     * @return string
     */
    public function getNetwork()
    {
        return (string) $this->data['network-code'];
    }

    /**
     * @return string
     */
    public function getId()
    {
        return (string) $this->data['messageId'];
    }

    /**
     * @return string
     */
    public function getReceiptFrom()
    {
        return (string) $this->data['msisdn'];
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->getReceiptFrom();
    }

    /**
     * @return string
     */
    public function getReceiptTo()
    {
        return (string) $this->data['to'];
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->getReceiptTo();
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return (string) $this->data['status'];
    }

    /**
     * @return string
     */
    public function getPrice()
    {
        return (string) $this->data['price'];
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp()
    {
        $date = \DateTime::createFromFormat('ymdHi', $this->data['scts']);
        if ($date) {
            return $date;
        }

        throw new \UnexpectedValueException('could not parse message timestamp');
    }

    /**
     * @return \DateTime
     */
    public function getSent()
    {
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $this->data['message-timestamp']);
        if ($date) {
            return $date;
        }

        throw new \UnexpectedValueException('could not parse message timestamp');
    }

    /**
     * @return string|null
     */
    public function getClientRef()
    {
        return $this->data['client-ref'];
    }
}
