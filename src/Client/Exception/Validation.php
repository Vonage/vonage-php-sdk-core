<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Client\Exception;

use Throwable;

class Validation extends Request
{
    /**
     * @var array
     */
    private $errors;

    /**
     * Validation constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param array $errors
     */
    public function __construct($message = '', $code = 0, Throwable $previous = null, array $errors = [])
    {
        $this->errors = $errors;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array
     */
    public function getValidationErrors(): array
    {
        return $this->errors;
    }
}
