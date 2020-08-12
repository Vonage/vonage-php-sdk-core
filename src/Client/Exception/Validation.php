<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Client\Exception;

use Throwable;

class Validation extends Request
{
    public function __construct($message = "", $code = 0, Throwable $previous = null, $errors)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    public function getValidationErrors()
    {
        return $this->errors;
    }
}
