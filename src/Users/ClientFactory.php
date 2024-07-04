<?php

declare(strict_types=1);

namespace Vonage\Users;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\KeypairHandler;
use Vonage\Entity\Hydrator\ArrayHydrator;

class ClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        $api = $container->make(APIResource::class);
        $api
            ->setBaseUri('/v1/users')
            ->setCollectionName('users')
            ->setAuthHandlers(new KeypairHandler());

        $hydrator = new ArrayHydrator();
        $hydrator->setPrototype(new User());

        return new Client($api, $hydrator);
    }
}
