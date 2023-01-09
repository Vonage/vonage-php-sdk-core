<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Messages;

use JsonException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Vonage\Client\Exception as ClientException;
use Vonage\Client\Exception\ThrottleException;

use function json_decode;

class ExceptionErrorHandler
{
    /**
     * @throws ClientException\Request
     * @throws ClientException\Server
     * @throws ThrottleException|JsonException
     */
    public function __invoke(ResponseInterface $response, RequestInterface $request)
    {
        $responseBody = json_decode(
            $response->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $statusCode = (int)$response->getStatusCode();

        if ($statusCode === 429) {
            throw new ThrottleException(
                $responseBody['title'] . ': ' . $responseBody['detail'],
                $response->getStatusCode()
            );
        }

        if ($statusCode >= 500 && $statusCode <= 599) {
            throw new ClientException\Server($responseBody['title'] . ': ' . $responseBody['detail']);
        }

        throw new ClientException\Request($responseBody['title'] . ': ' . $responseBody['detail']);
    }
}
