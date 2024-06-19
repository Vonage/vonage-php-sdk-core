<?php

declare(strict_types=1);

namespace Vonage\Account;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\CredentialsInterface;
use Vonage\Client\Credentials\Handler\BasicQueryHandler;
use Vonage\Client\HttpClient;

class ClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        /** @var CredentialsInterface $credentials */
        $credentials = $container->make('credentials');

        /** @var APIResource $accountApi */
        $accountApi = $container->make(APIResource::class);

        /** @var APIResource $accountApi */
        $httpClientLibrary = $container->make(HttpClient::class);

        $accountApi
            ->setBaseUrl('https://rest.nexmo.com')
            ->setCredentials($credentials)
            ->setIsHAL(false)
            ->setBaseUri('/account')
            ->setHttpClientLibrary($httpClientLibrary)
            ->setAuthHandler(new BasicQueryHandler())
        ;

        return new Client($accountApi);
    }
}
