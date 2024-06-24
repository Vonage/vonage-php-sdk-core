<?php

declare(strict_types=1);

namespace Vonage\Voice;

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
            ->setBaseUri('/v1/calls')
            ->setAuthHandlers(new KeypairHandler())
            ->setCollectionName('calls');

        return new Client($api);
    }
}
