<?php

namespace Vonage\SimSwap;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\SimSwapGnpHandler;

class ClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        $handler = new SimSwapGnpHandler();
        $handler->setBaseUrl('https://api-eu.vonage.com/oauth2/bc-authorize');
        $handler->setTokenUrl('https://api-eu.vonage.com/oauth2/token');

        $client = $container->get(\Vonage\Client::class);
        $handler->setClient($client);

        /** @var APIResource $api */
        $api = $container->make(APIResource::class);
        $api
            ->setBaseUrl('https://api-eu.vonage.com/camara/sim-swap/v040')
            ->setIsHAL(false)
            ->setClient($client)
            ->setErrorsOn200(false)
            ->setAuthHandlers($handler);

        return new Client($api);
    }
}