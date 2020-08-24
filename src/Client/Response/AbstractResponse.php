<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Client\Response;

abstract class AbstractResponse implements ResponseInterface
{
    protected $data;

    public function getData()
    {
        return $this->data;
    }

    public function isSuccess()
    {
        return isset($this->data['status']) and $this->data['status'] == 0;
    }

    public function isError()
    {
        return !$this->isSuccess();
    }
}
