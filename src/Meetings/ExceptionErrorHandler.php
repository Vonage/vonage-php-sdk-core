<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Meetings;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Vonage\Client\Exception\Conflict;
use Vonage\Client\Exception\Credentials;
use Vonage\Client\Exception\NotFound;
use Vonage\Client\Exception\Validation;

class ExceptionErrorHandler
{
    public function __invoke(ResponseInterface $response, RequestInterface $request): void
    {
        match ($response->getStatusCode()) {
            400 => throw new Validation('The request data was invalid'),
            403 => throw new Credentials('You are not authorised to perform this request'),
            404 => throw new NotFound('No resource found'),
            409 => throw new Conflict('Entity conflict')
        };
    }
}
