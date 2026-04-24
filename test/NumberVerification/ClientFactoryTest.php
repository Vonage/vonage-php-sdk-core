<?php

declare(strict_types=1);

namespace VonageTest\NumberVerification;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\APIResourceFactory;
use Vonage\Client\Factory\MapFactory;
use Vonage\NumberVerification\ClientFactory;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithConfiguredApiResource(): void
    {
        $mockClient = $this->createMock(Client::class);

        $mockServices = [
            'numberVerification' => ClientFactory::class,
            APIResource::class => APIResourceFactory::class,
            Client::class => fn () => $mockClient,
        ];

        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $result = $factory($container);
        $this->assertInstanceOf(\Vonage\NumberVerification\Client::class, $result);

        $reflection = new \ReflectionClass($result);
        $apiProperty = $reflection->getProperty('api');
        $apiResource = $apiProperty->getValue($result);

        $this->assertInstanceOf(Client\Credentials\Handler\NumberVerificationGnpHandler::class, $apiResource->getAuthHandlers()[0]);
        $this->assertFalse($apiResource->isHAL());
        $this->assertFalse($apiResource->errorsOn200());
        $this->assertEquals('https://api-eu.vonage.com/camara/number-verification/v031', $apiResource->getBaseUrl());

        $this->assertEquals('https://oidc.idp.vonage.com/oauth2/auth', $apiResource->getAuthHandlers()[0]->getBaseUrl());
        $this->assertEquals('https://api-eu.vonage.com/oauth2/token', $apiResource->getAuthHandlers()[0]->getTokenUrl());
        $this->assertEquals('openid+dpv:FraudPreventionAndDetection#number-verification-verify-read',
            $apiResource->getAuthHandlers()[0]->getScope()
        );

    }
}
