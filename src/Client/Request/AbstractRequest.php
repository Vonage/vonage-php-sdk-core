<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

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
