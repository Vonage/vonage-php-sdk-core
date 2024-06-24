<?php

declare(strict_types=1);

namespace Vonage\SMS;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\BasicHandler;
use Vonage\Client\Credentials\Handler\SignatureBodyHandler;

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
            ->setExceptionErrorHandler(new ExceptionErrorHandler())
            ->setAuthHandlers([new BasicHandler(), new SignatureBodyHandler()]);

        return new Client($api);
    }
}
