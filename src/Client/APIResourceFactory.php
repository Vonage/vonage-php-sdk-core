<?php
declare(strict_types=1);

namespace Vonage\Client;

use Psr\Container\ContainerInterface;
use Vonage\Client;

class APIResourceFactory
{
    public function __invoke(ContainerInterface $container): APIResource
    {
        return new APIResource($container->get(Client::class));
    }
}
