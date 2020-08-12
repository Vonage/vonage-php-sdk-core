<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Entity;

interface EntityInterface
{
    public function getRequest();

    public function getRequestData($sent = true);

    public function getResponse();

    public function getResponseData();

    public function setResponse(\Psr\Http\Message\ResponseInterface $response);

    public function setRequest(\Psr\Http\Message\RequestInterface $request);
}
