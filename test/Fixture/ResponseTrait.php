<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2017 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Fixture;

use Zend\Diactoros\Response;

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