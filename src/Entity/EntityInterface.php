<?php

declare(strict_types=1);

namespace Vonage\Entity;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface EntityInterface
{
    public function getRequest();

    public function getRequestData(bool $sent = true);

    public function getResponse();

    public function getResponseData();

    public function setResponse(ResponseInterface $response);

    public function setRequest(RequestInterface $request);
}
