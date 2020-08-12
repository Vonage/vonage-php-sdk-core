<?php
declare(strict_types=1);

namespace Vonage\Account;

use Vonage\Client\APIResource;
use Psr\Container\ContainerInterface;

class ClientFactory
{
    public function __invoke(ContainerInterface $container) : Client
    {
        /** @var APIResource $accountApi */
        $accountApi = $container->get(APIResource::class);
        $accountApi
            ->setBaseUrl($accountApi->getClient()->getRestUrl())
            ->setIsHAL(false)
            ->setBaseUri('/account')
        ;

        $secretsApi = $container->get(APIResource::class);
        $secretsApi->setBaseUri('/account');

        return new Client($accountApi, $secretsApi);
    }
}
