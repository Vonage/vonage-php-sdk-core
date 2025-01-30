<?php

declare(strict_types=1);

namespace Vonage\Account;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\BasicHandler;

class ClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        /** @var APIResource $accountApi */
        $accountApi = $container->make(APIResource::class);
        $accountApi
            ->setBaseUrl($accountApi->getClient()->getRestUrl())
            ->setIsHAL(false)
            ->setBaseUri('/account')
            ->setAuthHandlers(new BasicHandler())
        ;

        return new Client($accountApi);
    }
}
