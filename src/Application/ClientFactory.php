<?php

declare(strict_types=1);

namespace Vonage\Application;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\BasicHandler;

class ClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        /** @var APIResource $api */
        $api = $container->make(APIResource::class);
        $api
            ->setBaseUri('/v2/applications')
            ->setCollectionName('applications')
            ->setAuthHandlers(new BasicHandler());

        return new Client($api, new Hydrator());
    }
}
