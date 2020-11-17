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
    /**
     * @var array
     */
    protected $data;

    public function getData(): array
    {
        return $this->data;
    }

    public function isSuccess(): bool
    {
        return isset($this->data['status']) && (int)$this->data['status'] === 0;
    }

    public function isError(): bool
    {
        return !$this->isSuccess();
    }
}
