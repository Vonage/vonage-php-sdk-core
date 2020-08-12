<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Entity;

/**
 * Class Psr7Trait
 *
 * Allow an entity to contain last request / response objects.
 * 
 * @deprecated This information will no longer be available at the model level but the API client level
 */
trait NoRequestResponseTrait
{
    public function setResponse(\Psr\Http\Message\ResponseInterface $response)
    {
        throw new \RuntimeException(__CLASS__ . ' does not support request / response');
    }

    public function setRequest(\Psr\Http\Message\RequestInterface $request)
    {
        throw new \RuntimeException(__CLASS__ . ' does not support request / response');
    }

    public function getRequest()
    {
        return null;
    }

    public function getResponse()
    {
        return null;
    }
}
