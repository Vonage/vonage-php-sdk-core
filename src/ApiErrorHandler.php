<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage;

use Vonage\Client\Exception\Request as RequestException;
use Vonage\Client\Exception\Server as ServerException;
use Vonage\Client\Exception\Validation as ValidationException;

class ApiErrorHandler
{
    /**
     * @param string|int $statusCode
     *
     * @throws RequestException
     * @throws ServerException
     * @throws ValidationException
     */
    public static function check(array $body, $statusCode): void
    {
        $statusCodeType = (int)($statusCode / 100);

        // If it's ok, we can continue
        if ($statusCodeType === 2) {
            return;
        }

        // Build up our error message
        $errorMessage = $body['title'];

        if (isset($body['detail']) && $body['detail']) {
            $errorMessage .= ': ' . $body['detail'] . '.';
        } else {
            $errorMessage .= '.';
        }

        $errorMessage .= ' See ' . $body['type'] . ' for more information';

        // If it's a 5xx error, throw an exception
        if ($statusCodeType === 5) {
            throw new ServerException($errorMessage, $statusCode);
        }

        // Otherwise it's a 4xx, so we may have more context for the user
        // If it's a validation error, share that information
        if (isset($body['invalid_parameters'])) {
            throw new ValidationException($errorMessage, $statusCode, null, $body['invalid_parameters']);
        }

        // Otherwise throw a normal error
        throw new RequestException($errorMessage, $statusCode);
    }
}
