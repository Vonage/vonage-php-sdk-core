<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Voice\Message;

use Vonage\Client\Callback\CallbackInterface;
use Vonage\Client\Callback\Callback as BaseCallback;

/**
 * @deprecated This objects are no longer viable and will be removed in a future version
 */
class Callback extends BaseCallback implements CallbackInterface
{
    const TIME_FORMAT = 'Y-m-d H:i:s';

    protected $expected = array(
        'call-id',
        'status',
        'call-price',
        'call-rate',
        'call-duration',
        'to',
        'call-request',
        'network-code',
    );

    public function getId()
    {
        return $this->data['call-id'];
    }

    public function getTo()
    {
        return $this->data['to'];
    }

    public function getStatus()
    {
        return $this->data['status'];
    }

    public function getPrice()
    {
        return $this->data['call-price'];
    }

    public function getRate()
    {
        return $this->data['call-rate'];
    }

    public function getDuration()
    {
        return $this->data['call-duration'];
    }

    public function getCreated()
    {
        return \DateTime::createFromFormat(self::TIME_FORMAT, $this->data['call-request']);
    }

    public function getStart()
    {
        if (!isset($this->data['call-start'])) {
            return null;
        }

        return \DateTime::createFromFormat(self::TIME_FORMAT, $this->data['call-start']);
    }

    public function getEnd()
    {
        if (!isset($this->data['call-end'])) {
            return null;
        }

        return \DateTime::createFromFormat(self::TIME_FORMAT, $this->data['call-end']);
    }

    public function getNetwork()
    {
        return $this->data['network-code'];
    }
}
