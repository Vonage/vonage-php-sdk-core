<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2017 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Fixture;

use Laminas\Diactoros\Response;

/**
 * Creates mock response objects.
 * TODO: use example responses from API spec
 */
trait ResponseTrait
{
    protected function getResponse($type, $status = 200)
    {
        if(is_array($type)){
            $type = implode('/', $type);
        }

        return new Response(fopen(__DIR__ . '/../responses/' . $type . '.json', 'r'), $status);
    }

    protected function getResponseBody($type)
    {
        $response = $this->getResponse($type);
        return $response->getBody()->getContents();
    }

    protected function getResponseData($type)
    {
        $body = $this->getResponseBody($type);
        return json_decode($body, true);
    }
}
