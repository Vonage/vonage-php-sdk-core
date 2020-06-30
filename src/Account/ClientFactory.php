<?php
declare(strict_types=1);

namespace Nexmo\Account;

use Nexmo\Client\APIResource;
use Nexmo\Entity\Hydrator\ArrayHydrator;
use Psr\Container\ContainerInterface;

class ClientFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var APIResource $api */
        $accountApi = $container->get(APIResource::class);
        $accountApi
            ->setBaseUrl($api->getClient()->getRestUrl())
            ->setIsHAL(false)
            ->setBaseUri('/account')
        ;

        $secretsApi = $container->get(APIResource::class);
        $secretsApi->setBaseUri('/account');

        $priceFactory = new PriceFactory(new ArrayHydrator());

        return new Client($accountApi, $secretsApi, $priceFactory);
    }
}
