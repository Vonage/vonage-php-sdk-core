<?php

declare(strict_types=1);

namespace Vonage\Insights;

class Standard extends Basic
{

    public function getCurrentCarrier()
    {
        return $this->data['current_carrier'];
    }

    public function getOriginalCarrier()
    {
        return $this->data['original_carrier'];
    }

    public function getPorted()
    {
        return $this->data['ported'];
    }

    public function getRefundPrice()
    {
        return $this->data['refund_price'];
    }

    public function getRequestPrice()
    {
        return $this->data['request_price'];
    }

    public function getRemainingBalance()
    {
        return $this->data['remaining_balance'];
    }

    public function getRoaming()
    {
        return $this->data['roaming'];
    }
}
