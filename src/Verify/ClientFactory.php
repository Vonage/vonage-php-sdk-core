<?php

declare(strict_types=1);

namespace Vonage\Verify;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\TokenBodyHandler;

class ClientFactory
{

    public function __invoke(ContainerInterface $container): Client
    {
        /** @var APIResource $api */
        $api = $container->make(APIResource::class);
        $api
            ->setIsHAL(false)
            ->setBaseUri('/verify')
            ->setErrorsOn200(true)
            ->setAuthHandlers(new TokenBodyHandler())
            ->setExceptionErrorHandler(new ExceptionErrorHandler());

        return new Client($api);
    }
}
