<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Client\Response;

class Error extends Response
{
    public function __construct(array $data)
    {
        //normalize the data
        if (isset($data['error_text'])) {
            $data['error-text'] = $data['error_text'];
        }

        $this->expected = ['status', 'error-text'];

        parent::__construct($data);
    }

    public function isError(): bool
    {
        return true;
    }

    public function isSuccess(): bool
    {
        return false;
    }

    public function getCode(): int
    {
        return (int)$this->data['status'];
    }

    public function getMessage(): string
    {
        return (string)$this->data['error-text'];
    }
}
