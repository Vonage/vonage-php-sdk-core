<?php
declare(strict_types=1);

namespace Vonage\Insights;

use Vonage\Client\APIResource;
use Psr\Container\ContainerInterface;

class ClientFactory
{
    public function __invoke(ContainerInterface $container) : Client
    {
        /** @var APIResource $api */
        $api = $container->make(APIResource::class);
        $api->setIsHAL(false);

        return new Client($api);
    }
}
