<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Client\Exception;

use Throwable;

class Validation extends Request
{
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null, private array $errors = [])
    {
        parent::__construct($message, $code, $previous);
    }

    public function getValidationErrors(): array
    {
        return $this->errors;
    }
}
