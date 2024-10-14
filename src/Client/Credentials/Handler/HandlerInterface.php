<?php

namespace Vonage\Client\Credentials\Handler;

use Psr\Http\Message\RequestInterface;
use Vonage\Client\Credentials\CredentialsInterface;

interface HandlerInterface
{
    /**
     * Add authentication to a request
     */
    public function __invoke(RequestInterface $request, CredentialsInterface $credentials): RequestInterface;
}
