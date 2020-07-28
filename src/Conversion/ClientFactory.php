<?php
declare(strict_types=1);

namespace Nexmo\Conversion;

use Nexmo\Client\APIResource;
use Psr\Container\ContainerInterface;

/**
 * @todo Finish this Namespace
 */
class ClientFactory
{
    public function __invoke(ContainerInterface $container) : Client
    {
        /** @var APIResource $api */
        $api = $container->get(APIResource::class);
        $api->setBaseUri('/conversions/');

        return new Client($api);
    }
}
