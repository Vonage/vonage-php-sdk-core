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
    public function __invoke(ContainerInterface $container)
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
