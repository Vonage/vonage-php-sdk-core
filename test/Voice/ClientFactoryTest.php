<?php

declare(strict_types=1);

namespace VonageTest\Voice;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\APIResourceFactory;
use Vonage\Client\Factory\MapFactory;
use Vonage\Voice\ClientFactory;
class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithConfiguredApiResource(): void
    {
        $mockClient = $this->createMock(Client::class);

        $mockServices = [
            'voice' => ClientFactory::class,
            APIResource::class => APIResourceFactory::class,
            Client::class => fn() => $mockClient,
        ];

        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $result = $factory($container);
        $this->assertInstanceOf(\Vonage\voice\Client::class, $result);

        $reflection = new \ReflectionClass($result);
        $apiProperty = $reflection->getProperty('api');
        $apiResource = $apiProperty->getValue($result);

        $this->assertInstanceOf(Client\Credentials\Handler\KeypairHandler::class, $apiResource->getAuthHandlers()[0]);
        $this->assertEquals('/v1/calls', $apiResource->getBaseUri());
        $this->assertEquals('calls', $apiResource->getCollectionName());
    }
}
