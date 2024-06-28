<?php

declare(strict_types=1);

namespace Vonage\Entity;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * Class NoRequestResponseTrait
 *
 * Allow an entity to contain last request / response objects.
 *
 * @deprecated This information will no longer be available at the model level but the API client level
 */
trait NoRequestResponseTrait
{
    /**
     * @param ResponseInterface $response deprecated
     */
    public function setResponse(ResponseInterface $response): void
    {
        throw new RuntimeException(self::class . ' does not support request / response');
    }

    /**
     * @param RequestInterface $request deprecated
     */
    public function setRequest(RequestInterface $request): void
    {
        throw new RuntimeException(self::class . ' does not support request / response');
    }

    public function getRequest(): void
    {
    }

    public function getResponse(): void
    {
    }
}
