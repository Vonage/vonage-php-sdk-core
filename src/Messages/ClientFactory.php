<?php

namespace Vonage\Messages;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Keypair;

class ClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        /** @var APIResource $api */
        $api = $container->make(APIResource::class);
        $api
            ->setBaseUrl($api->getClient()->getApiUrl() . '/v1/messages')
            ->setIsHAL(false)
            ->setErrorsOn200(false)
            ->setExceptionErrorHandler(new ExceptionErrorHandler());

        $client = new Client($api);
        $client->setPreferredCredentialsClass(Keypair::class);

        return $client;
    }
}