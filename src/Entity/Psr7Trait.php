<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Entity;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;

/**
 * Class Psr7Trait
 *
 * Allow an entity to contain last request / response objects.
 */
trait Psr7Trait
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @param ResponseInterface $response
     */
    public function setResponse(ResponseInterface $response): void
    {
        trigger_error(
            get_class($this) . '::setResponse() is deprecated and will be removed',
            E_USER_DEPRECATED
        );

        $this->response = $response;
        $status = (int)$response->getStatusCode();

        if ($this instanceof ArrayHydrateInterface && (200 === $status || 201 === $status)) {
            $this->fromArray($this->getResponseData());
        }
    }

    /**
     * @param RequestInterface $request
     */
    public function setRequest(RequestInterface $request): void
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

    /**
     * @return RequestInterface|null
     */
    public function getRequest(): ?RequestInterface
    {
        trigger_error(
            get_class($this) . '::getRequest() is deprecated. ' .
            'Please get the APIResource from the appropriate client to get this information',
            E_USER_DEPRECATED
        );

        return $this->request;
    }

    /**
     * @return ResponseInterface|null
     */
    public function getResponse(): ?ResponseInterface
    {
        trigger_error(
            get_class($this) . '::getResponse() is deprecated. ' .
            'Please get the APIResource from the appropriate client to get this information',
            E_USER_DEPRECATED
        );

        return $this->response;
    }
}
