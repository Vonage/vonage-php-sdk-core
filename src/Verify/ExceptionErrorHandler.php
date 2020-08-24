<?php
declare(strict_types = 1);

namespace Vonage\Verify;

use Vonage\Client\Exception\Server;
use Vonage\Client\Exception\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Error handler for API requests returned by the Verify API
 */
class ExceptionErrorHandler
{
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
                $e = new Server($data['error_text'], (int) $data['status']);
                $e->setEntity($data);
                throw $e;
                break;
            default:
                $e = new Request($data['error_text'], (int) $data['status']);
                $e->setEntity($data);
                throw $e;
                break;
        }

        return $e;
    }
}