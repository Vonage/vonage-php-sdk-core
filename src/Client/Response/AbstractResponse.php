<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
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
        return isset($this->data['status']) && (int)$this->data['status'] === 0;
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return !$this->isSuccess();
    }
}
