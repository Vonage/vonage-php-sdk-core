<?php
namespace Vonage\Insights;

class Advanced extends Standard
{
    public function getValidNumber()
    {
        return $this->data['valid_number'];
    }

    public function getReachable()
    {
        return $this->data['reachable'];
    }
}
