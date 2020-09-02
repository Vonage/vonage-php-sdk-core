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
        $accountApi = $container->make(APIResource::class);
        $accountApi
            ->setBaseUrl($accountApi->getClient()->getRestUrl())
            ->setIsHAL(false)
            ->setBaseUri('/account')
        ;

        $secretsApi = $container->make(APIResource::class);
        $secretsApi->setBaseUri('/accounts');

        return new Client($accountApi, $secretsApi);
    }
}
