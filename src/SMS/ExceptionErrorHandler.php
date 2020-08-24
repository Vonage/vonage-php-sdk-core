<?php
declare(strict_types=1);

namespace Vonage\SMS;

use Vonage\Client\Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Vonage\Client\Exception\ThrottleException;

class ExceptionErrorHandler
{
    public function __invoke(ResponseInterface $response, RequestInterface $request)
    {
        //check for valid data, as well as an error response from the API
        if ($response->getStatusCode() == '429') {
            throw new ThrottleException('Too many concurrent requests', $response->getStatusCode());
        }

        $data = json_decode($response->getBody()->getContents(), true);
        if (!isset($data['messages'])) {
            if (isset($data['error-code']) && isset($data['error-code-label'])) {
                $e = new Exception\Request($data['error-code-label'], (int) $data['error-code']);
            } else {
                $e = new Exception\Request('unexpected response from API');
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

                    if (preg_match('#\[\s+(\d+)\s+\]#', $part['error-text'], $match)) {
                        $e->setTimeout((int) $match[1] + 1);
                    }

                    throw $e;
                case '5':
                    $e = new Exception\Server($part['error-text'], (int) $part['status']);
                    $e->setEntity($data);
                    throw $e;
                default:
                    $e = new Exception\Request($part['error-text'], (int) $part['status']);
                    $e->setEntity($data);
                    throw $e;
            }
        }
    }
}
