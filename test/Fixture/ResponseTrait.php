<?php

declare(strict_types=1);

namespace VonageTest\Fixture;

use Laminas\Diactoros\Response;

use function fopen;
use function implode;
use function is_array;
use function json_decode;

/**
 * Creates mock response objects.
 * TODO: use example responses from API spec
 */
trait ResponseTrait
{
    /**
     * @param string|array $type
     */
    protected function getResponse($type = 'success', int $status = 200): Response
    {
        if (is_array($type)) {
            $type = implode('/', $type);
        }

        return new Response(fopen(__DIR__ . '/../responses/' . $type . '.json', 'rb'), $status);
    }

    /**
     * @param $type
     */
    protected function getResponseBody($type): string
    {
        $response = $this->getResponse($type);

        return $response->getBody()->getContents();
    }

    /**
     * @param $type
     */
    protected function getResponseData($type)
    {
        $body = $this->getResponseBody($type);

        return json_decode((string) $body, true);
    }
}
