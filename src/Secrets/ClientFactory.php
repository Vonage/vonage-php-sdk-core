<?php

declare(strict_types=1);

namespace Vonage\Secrets;

use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\BasicHandler;
use Vonage\Client\Factory\FactoryInterface;

class ClientFactory
{
    public function __invoke(FactoryInterface $container): Client
    {
        $api = $container->make(APIResource::class);
        $api->setBaseUri('/accounts')
            ->setAuthHandlers(new BasicHandler())
            ->setCollectionName('secrets');

        return new Client($api);
    }
}
