<?php
declare(strict_types=1);

namespace Vonage\Conversion;

use Vonage\Client\APIResource;
use Psr\Container\ContainerInterface;

/**
 * @todo Finish this Namespace
 */
class ClientFactory
{
    public function __invoke(ContainerInterface $container) : Client
    {
        /** @var APIResource $api */
        $api = $container->make(APIResource::class);
        $api->setBaseUri('/conversions/');

        return new Client($api);
    }
}
