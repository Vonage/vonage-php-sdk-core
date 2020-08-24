<?php
declare(strict_types=1);

namespace Vonage\Client;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class APIExceptionHandler
{
    /**
     * Format to use for the rfc7807 formatted errors
     * @var string
     */
    protected $rfc7807Format = "%s: %s. See %s for more information";

    /**
     * @param string $format sprintf() format to use for error messages
     */
    public function setRfc7807Format(string $format)
    {
        $this->rfc7807Format = $format;
    }

    public function __invoke(ResponseInterface $response, RequestInterface $request)
    {
        $body = json_decode($response->getBody()->getContents(), true);
        $response->getBody()->rewind();
        $status = $response->getStatusCode();

        // Error responses aren't consistent. Some are generated within the
        // proxy and some are generated within voice itself. This handles
        // both cases

        // This message isn't very useful, but we shouldn't ever see it
        $errorTitle = 'Unexpected error';

        if (isset($body['title'])) {
            // Have to do this check to handle VAPI errors
            if (isset($body['type']) && is_string($body['type'])) {
                $errorTitle = sprintf(
                    $this->rfc7807Format,
                    $body['title'],
                    $body['detail'],
                    $body['type']
                );
            } else {
                $errorTitle = $body['title'];
            }
        }

        if (isset($body['error_title'])) {
            $errorTitle = $body['error_title'];
        }

        if (isset($body['error-code-label'])) {
            $errorTitle = $body['error-code-label'];
        }

        if (isset($body['description'])) {
            $errorTitle = $body['description'];
        }

        if ($status >= 400 and $status < 500) {
            $e = new Exception\Request($errorTitle, $status);
            @$e->setRequest($request);
            @$e->setResponse($response);
        } elseif ($status >= 500 and $status < 600) {
            $e = new Exception\Server($errorTitle, $status);
            @$e->setRequest($request);
            @$e->setResponse($response);
        } else {
            $e = new Exception\Exception('Unexpected HTTP Status Code');
            throw $e;
        }

        return $e;
    }
}
