<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Client\Request;

abstract class AbstractRequest implements RequestInterface
{
    protected $params = [];

    /**
     * @return array
     */
    public function getParams(): array
    {
        return array_filter($this->params, 'is_scalar');
    }
}
