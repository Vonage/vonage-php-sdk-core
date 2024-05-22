<?php

declare(strict_types=1);

namespace Vonage\Insights;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\BasicQueryHandler;

class ClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        /** @var APIResource $api */
        $api = $container->make(APIResource::class);
        $api->setIsHAL(false);
        $api->setAuthHandlers(new BasicQueryHandler());

        return new Client($api);
    }
}
