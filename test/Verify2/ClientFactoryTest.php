<?php

declare(strict_types=1);

namespace VonageTest\Verify2;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\APIResourceFactory;
use Vonage\Client\Factory\MapFactory;
use Vonage\Verify2\ClientFactory;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithConfiguredApiResource(): void
    {
        $mockClient = $this->createMock(Client::class);

        $mockServices = [
            'verify2' => ClientFactory::class,
            APIResource::class => APIResourceFactory::class,
            Client::class => fn() => $mockClient,
        ];

        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $result = $factory($container);
        $this->assertInstanceOf(\Vonage\Verify2\Client::class, $result);

        $reflection = new \ReflectionClass($result);
        $apiProperty = $reflection->getProperty('api');
        $apiResource = $apiProperty->getValue($result);

        $this->assertInstanceOf(Client\Credentials\Handler\KeypairHandler::class, $apiResource->getAuthHandlers()[0]);
        $this->assertInstanceOf(Client\Credentials\Handler\BasicHandler::class, $apiResource->getAuthHandlers()[1]);
        $this->assertEquals('https://api.nexmo.com/v2/verify', $apiResource->getBaseUrl());
        $this->assertFalse($apiResource->errorsOn200());
        $this->assertFalse($apiResource->isHAL());
    }
}
