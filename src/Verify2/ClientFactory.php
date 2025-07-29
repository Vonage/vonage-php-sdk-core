<?php

namespace Vonage\Verify2;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\BasicHandler;
use Vonage\Client\Credentials\Handler\KeypairHandler;

class ClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        $api = $container->make(APIResource::class);
        $api->setIsHAL(false)
            ->setErrorsOn200(false)
            ->setAuthHandlers([new KeypairHandler(), new BasicHandler()])
            ->setBaseUri('/v2/verify');

        return new Client($api);
    }
}
