<?php

declare(strict_types=1);

namespace Vonage\Insights;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\BasicHandler;

class ClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        /** @var APIResource $api */
        $api = $container->make(APIResource::class);
        $api->setIsHAL(false);
        $api->setAuthHandler(new BasicHandler());

        return new Client($api);
    }
}
