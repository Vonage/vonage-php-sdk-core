<?php

namespace Vonage\Client\Credentials\Handler;

use Psr\Http\Message\RequestInterface;
use Vonage\Client\Credentials\Container;
use Vonage\Client\Credentials\CredentialsInterface;

abstract class AbstractHandler implements HandlerInterface
{
    abstract function __invoke(RequestInterface $request, CredentialsInterface $credentials): RequestInterface;

    protected function extract(string $class, CredentialsInterface $credentials): CredentialsInterface
    {
        if ($credentials instanceof $class) {
            return $credentials;
        }

        if ($credentials instanceof Container) {
            $creds = $credentials->get($class);
            if (!is_null($creds)) {
                return $creds;
            }
        }

        throw new \RuntimeException('Requested auth type not found');
    }
}
