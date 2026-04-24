<?php

declare(strict_types=1);

namespace Vonage\Client\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Exception extends \Exception
{
    protected RequestInterface $request;
    protected ResponseInterface $response;
    protected mixed $entity = null;

    public function setRequest(RequestInterface $request): void
    {
        $this->request = $request;
    }

    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }

    public function setEntity(mixed $entity): void
    {
        $this->entity = $entity;
    }

    public function getEntity(): mixed
    {
        return $this->entity;
    }
}
