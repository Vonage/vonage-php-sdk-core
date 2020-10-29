<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Insights;

class Standard extends Basic
{
    /**
     * @return mixed
     */
    public function getCurrentCarrier()
    {
        return $this->data['current_carrier'];
    }

    /**
     * @return mixed
     */
    public function getOriginalCarrier()
    {
        return $this->data['original_carrier'];
    }

    /**
     * @return mixed
     */
    public function getPorted()
    {
        return $this->data['ported'];
    }

    /**
     * @return mixed
     */
    public function getRefundPrice()
    {
        return $this->data['refund_price'];
    }

    /**
     * @return mixed
     */
    public function getRequestPrice()
    {
        return $this->data['request_price'];
    }

    /**
     * @return mixed
     */
    public function getRemainingBalance()
    {
        return $this->data['remaining_balance'];
    }

    /**
     * @return mixed
     */
    public function getRoaming()
    {
        return $this->data['roaming'];
    }
}
