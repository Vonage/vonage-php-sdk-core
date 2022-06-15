<?php

namespace Vonage\Messages;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;

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

        return new Client($api);
    }
}