<?php

declare(strict_types=1);

namespace Vonage\Insights;

class Advanced extends Standard
{
    public function getValidNumber(): mixed
    {
        return $this->data['valid_number'];
    }

    public function getReachable(): mixed
    {
        return $this->data['reachable'];
    }
}
