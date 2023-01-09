<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Entity;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;

use function array_merge;
use function get_class;
use function is_array;
use function json_decode;
use function method_exists;
use function parse_str;
use function trigger_error;

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

    public function setResponse(ResponseInterface $response): void
    {
        trigger_error(
            $this::class . '::setResponse() is deprecated and will be removed',
            E_USER_DEPRECATED
        );

        $this->response = $response;
        $status = (int)$response->getStatusCode();

        if ($this instanceof ArrayHydrateInterface && (200 === $status || 201 === $status)) {
            $this->fromArray($this->getResponseData());
        }
    }

    public function setRequest(RequestInterface $request): void
    {
        trigger_error(
            $this::class . '::setRequest is deprecated and will be removed',
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

    public function getRequest(): ?RequestInterface
    {
        trigger_error(
            $this::class . '::getRequest() is deprecated. ' .
            'Please get the APIResource from the appropriate client to get this information',
            E_USER_DEPRECATED
        );

        return $this->request;
    }

    public function getResponse(): ?ResponseInterface
    {
        trigger_error(
            $this::class . '::getResponse() is deprecated. ' .
            'Please get the APIResource from the appropriate client to get this information',
            E_USER_DEPRECATED
        );

        return $this->response;
    }
}
