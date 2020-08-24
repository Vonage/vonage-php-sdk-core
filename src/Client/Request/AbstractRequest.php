<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Client\Request;

abstract class AbstractRequest implements RequestInterface
{
    protected $params = array();

    /**
     * @return array
     */
    public function getParams()
    {
        return array_filter($this->params, 'is_scalar');
    }
}
