<?php

namespace Vonage\ProactiveConnect;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\KeypairHandler;

class ClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        $api = $container->make(APIResource::class);
        $api->setIsHAL(false)
            ->setErrorsOn200(false)
            ->setAuthHandlers([new KeypairHandler()])
            ->setBaseUrl('https://api-eu.vonage.com/v0.1/bulk/');

        return new Client($api);
    }
}
