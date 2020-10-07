<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Client\Response;

abstract class AbstractResponse implements ResponseInterface
{
    protected $data;

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return isset($this->data['status']) and (int)$this->data['status'] === 0;
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return !$this->isSuccess();
    }
}
