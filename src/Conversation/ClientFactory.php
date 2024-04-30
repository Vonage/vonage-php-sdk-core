<?php

namespace Vonage\Conversation;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\KeypairHandler;
use Vonage\Verify2\Client;

class ClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        $api = $container->make(APIResource::class);
        $api->setIsHAL(true)
            ->setErrorsOn200(false)
            ->setAuthHandler(new KeypairHandler())
            ->setBaseUrl('https://api.nexmo.com/v1/conversations');

        return new Client($api);
    }
}