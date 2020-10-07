<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Verify;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Vonage\Client\Exception\Request;
use Vonage\Client\Exception\Server;

/**
 * Error handler for API requests returned by the Verify API
 */
class ExceptionErrorHandler
{
    /**
     * @param ResponseInterface $response
     * @param RequestInterface $request
     * @throws Request
     * @throws Server
     */
    public function __invoke(ResponseInterface $response, RequestInterface $request)
    {
        $data = json_decode($response->getBody()->getContents(), true);
        $response->getBody()->rewind();
        $e = null;

        if (!isset($data['status'])) {
            $e = new Request('unexpected response from API');
            $e->setEntity($data);
            throw $e;
        }

        //normalize errors (client vrs server)
        switch ($data['status']) {
            // These exist because `status` is valid in both the error
            // response and a success response, but serve different purposes
            // in each case
            case 'IN PROGRESS':
            case 'SUCCESS':
            case 'FAILED':
            case 'EXPIRED':
            case 'CANCELLED':
            case '0':
                break;
            case '5':
            default:
                $e = new Server($data['error_text'], (int)$data['status']);
                $e->setEntity($data);
                throw $e;
        }
    }
}
