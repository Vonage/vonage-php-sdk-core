<?php

declare(strict_types=1);

namespace VonageTest\ProactiveConnect;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Factory\MapFactory;
use Vonage\ProactiveConnect\ClientFactory;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithConfiguredApiResource(): void
    {
        $mockClient = $this->createMock(Client::class);

        $mockServices = [
            'proactiveConnect' => ClientFactory::class,
            APIResource::class => APIResource::class,
        ];

        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $result = @$factory($container);
        $this->assertInstanceOf(\Vonage\ProactiveConnect\Client::class, $result);

        $reflection = new \ReflectionClass($result);
        $apiProperty = $reflection->getProperty('api');
        $apiResource = $apiProperty->getValue($result);

        $this->assertInstanceOf(Client\Credentials\Handler\KeypairHandler::class, $apiResource->getAuthHandlers()[0]);
        $this->assertEquals('https://api-eu.vonage.com/v0.1/bulk/', $apiResource->getBaseUrl());
        $this->assertFalse($apiResource->isHAL());
        $this->assertFalse($apiResource->errorsOn200());
    }
}
