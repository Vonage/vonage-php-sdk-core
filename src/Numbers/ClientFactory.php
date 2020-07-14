<?php
declare(strict_types=1);

namespace Nexmo\Numbers;

use Nexmo\Client\APIResource;
use Psr\Container\ContainerInterface;

class ClientFactory
{
    public function __invoke(ContainerInterface $container) : Client
    {
        /** @var APIResource $api */
        $api = $container->get(APIResource::class);
        $api
            ->setBaseUrl($api->getClient()->getRestUrl())
            ->setIsHAL(false)
        ;

        return new Client($api);
    }
}
