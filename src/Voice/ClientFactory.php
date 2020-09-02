<?php
declare(strict_types=1);

namespace Vonage\Voice;

use Vonage\Client\APIResource;
use Psr\Container\ContainerInterface;

class ClientFactory
{
    public function __invoke(ContainerInterface $container) : Client
    {
        /** @var APIResource $api */
        $api = $container->make(APIResource::class);
        $api
            ->setBaseUri('/v1/calls')
            ->setCollectionName('calls')
        ;

        return new Client($api);
    }
}
