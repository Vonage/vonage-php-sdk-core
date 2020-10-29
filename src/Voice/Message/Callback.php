<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Voice\Message;

use DateTime;
use Vonage\Client\Callback\Callback as BaseCallback;

/**
 * @deprecated This objects are no longer viable and will be removed in a future version
 */
class Callback extends BaseCallback
{
    public const TIME_FORMAT = 'Y-m-d H:i:s';

    protected $expected = [
        'call-id',
        'status',
        'call-price',
        'call-rate',
        'call-duration',
        'to',
        'call-request',
        'network-code',
    ];

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

    /**
     * @return DateTime|false
     */
    public function getCreated()
    {
        return DateTime::createFromFormat(self::TIME_FORMAT, $this->data['call-request']);
    }

    /**
     * @return DateTime|false|null
     */
    public function getStart()
    {
        if (!isset($this->data['call-start'])) {
            return null;
        }

        return DateTime::createFromFormat(self::TIME_FORMAT, $this->data['call-start']);
    }

    /**
     * @return DateTime|false|null
     */
    public function getEnd()
    {
        if (!isset($this->data['call-end'])) {
            return null;
        }

        return DateTime::createFromFormat(self::TIME_FORMAT, $this->data['call-end']);
    }

    public function getNetwork()
    {
        return $this->data['network-code'];
    }
}
