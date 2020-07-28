<?php
namespace Nexmo\Insights;

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

    public function getRoaming()
    {
        return $this->data['roaming'];
    }
}
