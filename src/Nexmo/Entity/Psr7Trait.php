<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Entity;

/**
 * Class Psr7Trait
 *
 * Allow an entity to contain request / response objects.
 */
trait Psr7Trait
{
    /**
     * @var \Psr\Http\Message\RequestInterface
     */
    protected $request;

    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    public function setResponse(\Psr\Http\Message\ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function setRequest(\Psr\Http\Message\RequestInterface $request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getResponse()
    {
        return $this->response;
    }
}