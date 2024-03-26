<?php

namespace Vonage\Subaccount;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\BasicHandler;
use Vonage\Client\Credentials\Handler\KeypairHandler;

class ClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        $api = $container->make(APIResource::class);
        $api->setIsHAL(true)
            ->setErrorsOn200(false)
            ->setBaseUrl('https://api.nexmo.com/accounts');

        return new Client($api);
    }
}