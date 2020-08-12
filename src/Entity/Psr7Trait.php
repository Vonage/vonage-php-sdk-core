<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Entity;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

/**
 * Class Psr7Trait
 *
 * Allow an entity to contain last request / response objects.
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
        trigger_error(
            get_class($this) . '::setResponse() is deprecated and will be removed',
            E_USER_DEPRECATED
        );
        $this->response = $response;

        $status = $response->getStatusCode();

        if ($this instanceof ArrayHydrateInterface and ((200 == $status) or (201 == $status))) {
            $this->fromArray($this->getResponseData());
        }
    }

    public function setRequest(\Psr\Http\Message\RequestInterface $request)
    {
        trigger_error(
            get_class($this) . '::setRequest is deprecated and will be removed',
            E_USER_DEPRECATED
        );
        $this->request = $request;
        $this->data = [];

        if (method_exists($request, 'getQueryParams')) {
            $this->data = $request->getQueryParams();
        }

        $contentType = $request->getHeader('Content-Type');
        if (!empty($contentType)) {
            if ($contentType[0] === 'application/json') {
                $body = json_decode($request->getBody()->getContents(), true);
                if (is_array($body)) {
                    $this->data = array_merge(
                        $this->data,
                        $body
                    );
                }
            }
        } else {
            parse_str($request->getBody()->getContents(), $body);
            $this->data = array_merge($this->data, $body);
        }
        
    }

    public function getRequest()
    {
        trigger_error(
            get_class($this) . '::getRequest() is deprecated. Please get the APIResource from the appropriate client to get this information',
            E_USER_DEPRECATED
        );
        return $this->request;
    }

    public function getResponse()
    {
        trigger_error(
            get_class($this) . '::getResponse() is deprecated. Please get the APIResource from the appropriate client to get this information',
            E_USER_DEPRECATED
        );
        return $this->response;
    }
}
