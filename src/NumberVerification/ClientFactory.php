<?php

namespace Vonage\NumberVerification;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\NumberVerificationGnpHandler;

class ClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        $handler = new NumberVerificationGnpHandler();
        $handler->setBaseUrl('https://oidc.idp.vonage.com/oauth2/auth');
        $handler->setTokenUrl('https://api-eu.vonage.com/oauth2/token');
        $handler->setScope('openid+dpv:FraudPreventionAndDetection#number-verification-verify-read');

        $client = $container->get(\Vonage\Client::class);
        $handler->setClient($client);

        /** @var APIResource $api */
        $api = $container->make(APIResource::class);
        $api
            ->setBaseUrl('https://api-eu.vonage.com/camara/number-verification/v031')
            ->setIsHAL(false)
            ->setClient($client)
            ->setErrorsOn200(false)
            ->setAuthHandlers($handler);

        return new Client($api);
    }
}