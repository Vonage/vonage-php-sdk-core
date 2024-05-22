<?php

declare(strict_types=1);

namespace Vonage\Meetings;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\KeypairHandler;

class ClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        /** @var APIResource $api */
        $api = $container->make(APIResource::class);
        $api
            ->setBaseUrl('https://api-eu.vonage.com/v1/meetings/')
            ->setExceptionErrorHandler(new ExceptionErrorHandler())
            ->setAuthHandlers(new KeypairHandler());

        return new Client($api);
    }
}
