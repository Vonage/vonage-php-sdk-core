<?php

declare(strict_types=1);

namespace Vonage\Entity;

trait ArrayAccessTrait
{
    abstract public function getResponseData();

    abstract public function getRequestData();
}
