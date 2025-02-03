<?php

declare(strict_types=1);

namespace VonageTest\NumberVerification;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Factory\MapFactory;
use Vonage\NumberVerification\ClientFactory;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithConfiguredApiResource(): void
    {
        $mockClient = $this->createMock(Client::class);

        $mockServices = [
            'numberVerification' => ClientFactory::class,
            APIResource::class => APIResource::class,
            Client::class => fn () => $mockClient,
        ];

        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $result = $factory($container);
        $this->assertInstanceOf(\Vonage\NumberVerification\Client::class, $result);
        $this->assertInstanceOf(Client\Credentials\Handler\NumberVerificationGnpHandler::class, $result->getAPIResource()
            ->getAuthHandlers()[0]);
        $this->assertFalse($result->getAPIResource()->isHAL());
        $this->assertFalse($result->getAPIResource()->errorsOn200());
        $this->assertEquals('https://api-eu.vonage.com/camara/number-verification/v031', $result->getAPIResource()
            ->getBaseUrl());

        $this->assertEquals('https://oidc.idp.vonage.com/oauth2/auth', $result->getAPIResource()->getAuthHandlers()[0]->getBaseUrl());
        $this->assertEquals('https://api-eu.vonage.com/oauth2/token', $result->getAPIResource()->getAuthHandlers()
        [0]->getTokenUrl());
        $this->assertEquals('openid+dpv:FraudPreventionAndDetection#number-verification-verify-read',
            $result->getAPIResource()->getAuthHandlers()[0]->getScope());

    }
}
