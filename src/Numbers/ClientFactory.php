<?php

declare(strict_types=1);

namespace Vonage\Numbers;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\BasicHandler;
use Vonage\Entity\Hydrator\ArrayHydrator;

class ClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        /** @var APIResource $api */
        $api = $container->make(APIResource::class);
        $api
            ->setBaseUrl($api->getRestURL())
            ->setIsHAL(false)
            ->setAuthHandlers(new BasicHandler());

        $hydrator = new ArrayHydrator();
        $hydrator->setPrototype(new Number());

        return new Client($api, $hydrator);
    }
}
