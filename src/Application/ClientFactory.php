<?php
declare(strict_types=1);

namespace Nexmo\Application;

use Nexmo\Client\APIResource;
use Psr\Container\ContainerInterface;

class ClientFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var APIResource $api */
        $api = $container->get(APIResource::class);
        $api
            ->setBaseUri('/v2/applications')
            ->setCollectionName('applications')
        ;

        return new Client($api, new Hydrator());
    }
}
