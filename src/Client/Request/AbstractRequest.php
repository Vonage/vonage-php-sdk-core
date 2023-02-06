<?php

declare(strict_types=1);

namespace Vonage\Client\Request;

use function array_filter;

abstract class AbstractRequest implements RequestInterface
{
    /**
     * @var array
     */
    protected $params = [];

    public function getParams(): array
    {
        return array_filter($this->params, 'is_scalar');
    }
}
