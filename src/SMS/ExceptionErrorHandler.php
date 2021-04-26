<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\SMS;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Vonage\Client\Exception as ClientException;
use Vonage\Client\Exception\ThrottleException;

use function json_decode;
use function preg_match;

class ExceptionErrorHandler
{
    /**
     * @throws ClientException\Request
     * @throws ClientException\Server
     * @throws ThrottleException
     */
    public function __invoke(ResponseInterface $response, RequestInterface $request)
    {
        //check for valid data, as well as an error response from the API
        if ((int)$response->getStatusCode() === 429) {
            throw new ThrottleException('Too many concurrent requests', $response->getStatusCode());
        }

        $data = json_decode($response->getBody()->getContents(), true);

        if (!isset($data['messages'])) {
            if (isset($data['error-code'], $data['error-code-label'])) {
                $e = new ClientException\Request($data['error-code-label'], (int)$data['error-code']);
            } else {
                $e = new ClientException\Request('unexpected response from API');
            }

            $e->setEntity($data);
            throw $e;
        }

        //normalize errors (client vrs server)
        foreach ($data['messages'] as $part) {
            switch ($part['status']) {
                case '0':
                    break; //all okay
                case '1':
                    $e = new ThrottleException($part['error-text']);
                    $e->setTimeout(1);
                    $e->setEntity($data);

                    if (preg_match('#Throughput Rate Exceeded - please wait \[\s+(\d+)\s+] and retry#', $part['error-text'], $match)) {
                        $seconds = max((int)$match[1] / 1000, 1);
                        $e->setTimeout($seconds);
                    }

                    throw $e;
                case '5':
                    $e = new ClientException\Server($part['error-text'], (int)$part['status']);
                    $e->setEntity($data);
                    throw $e;
                default:
                    $e = new ClientException\Request($part['error-text'], (int)$part['status']);
                    $e->setEntity($data);
                    throw $e;
            }
        }
    }
}
