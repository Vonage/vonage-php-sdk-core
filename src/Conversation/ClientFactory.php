<?php

namespace Vonage\Conversation;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\KeypairHandler;

class ClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        /** @var APIResource $api */
        $api = $container->make(APIResource::class);
        $api->setIsHAL(true)
            ->setErrorsOn200(false)
            ->setAuthHandlers(new KeypairHandler())
            ->setBaseUrl('https://api.nexmo.com/v1/conversations');

        return new Client($api);
    }
}
