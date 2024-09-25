<?php

declare(strict_types=1);

namespace Vonage\Insights;

class Standard extends Basic
{
    public function getCurrentCarrier(): mixed
    {
        return $this->data['current_carrier'];
    }

    public function getOriginalCarrier(): mixed
    {
        return $this->data['original_carrier'];
    }

    public function getPorted(): mixed
    {
        return $this->data['ported'];
    }

    public function getRefundPrice(): mixed
    {
        return $this->data['refund_price'];
    }

    public function getRequestPrice(): mixed
    {
        return $this->data['request_price'];
    }

    public function getRemainingBalance(): mixed
    {
        return $this->data['remaining_balance'];
    }

    public function getRoaming(): mixed
    {
        return $this->data['roaming'];
    }
}
