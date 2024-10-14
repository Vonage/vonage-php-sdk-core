<?php

declare(strict_types=1);

namespace Vonage\Client\Request;

interface RequestInterface
{
    public function getParams(): array;

    public function getURI(): string;
}
