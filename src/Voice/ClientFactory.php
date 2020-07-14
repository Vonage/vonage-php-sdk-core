<?php
declare(strict_types=1);

namespace Nexmo\Voice;

use Nexmo\Client\APIResource;
use Psr\Container\ContainerInterface;

class ClientFactory
{
    public function __invoke(ContainerInterface $container) : Client
    {
        /** @var APIResource $api */
        $api = $container->get(APIResource::class);
        $api
            ->setBaseUri('/v1/calls')
            ->setCollectionName('calls')
        ;

        return new Client($api);
    }
}
