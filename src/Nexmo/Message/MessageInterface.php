<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Message;

interface MessageInterface extends \Countable, \ArrayAccess, \Iterator
{
    public function getRequest();

    public function getRequestData($sent = true);

    public function getResponse();

    public function getResponseData();

    public function setResponse(\Psr\Http\Message\ResponseInterface $response);

    public function setRequest(\Psr\Http\Message\RequestInterface $request);

    public function requestDLR($dlr = true);

    public function setClientRef($ref);

    public function setNetwork($network);

    public function setTTL($ttl);

    public function setClass($class);
}