<?php

namespace Vonage\Messages;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;
use Vonage\SMS\ExceptionErrorHandler;

class ClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        /** @var APIResource $api */
        $api = $container->make(APIResource::class);
        $api
            ->setBaseUrl($api->getClient()->getRestUrl())
            ->setCollectionName('messages')
            ->setIsHAL(false)
            ->setErrorsOn200(true)
            ->setExceptionErrorHandler(new ExceptionErrorHandler());

        return new Client($api);
    }
}