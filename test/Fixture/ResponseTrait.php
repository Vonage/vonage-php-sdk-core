<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\Test\Fixture;

use Laminas\Diactoros\Response;

/**
 * Creates mock response objects.
 * TODO: use example responses from API spec
 */
trait ResponseTrait
{
    /**
     * @param string|array $type
     * @param int $status
     * @return Response
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
     * @return string
     */
    protected function getResponseBody($type): string
    {
        $response = $this->getResponse($type);

        return $response->getBody()->getContents();
    }

    /**
     * @param $type
     * @return mixed
     */
    protected function getResponseData($type)
    {
        $body = $this->getResponseBody($type);

        return json_decode($body, true);
    }
}
