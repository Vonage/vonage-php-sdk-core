<?php
namespace Vonage\Insights;

trait CnamTrait
{
    public function getCallerName()
    {
        return $this->data['caller_name'];
    }

    public function getFirstName()
    {
        return $this->data['first_name'];
    }

    public function getLastName()
    {
        return $this->data['last_name'];
    }

    public function getCallerType()
    {
        return $this->data['caller_type'];
    }
}
