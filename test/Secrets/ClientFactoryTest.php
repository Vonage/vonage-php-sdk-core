<?php

declare(strict_types=1);

namespace VonageTest\Secrets;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\APIResourceFactory;
use Vonage\Client\Factory\MapFactory;
use Vonage\Secrets\ClientFactory;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithProperConfiguration(): void
    {
        $mockClient = $this->createMock(Client::class);

        $mockServices = [
            'secrets' => ClientFactory::class,
            APIResource::class => APIResourceFactory::class,
            Client::class => fn() => $mockClient,
        ];

        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $client = $factory($container);
        $this->assertInstanceOf(\Vonage\Secrets\Client::class, $client);

        $reflection = new \ReflectionClass($client);
        $apiProperty = $reflection->getProperty('api');
        $apiResource = $apiProperty->getValue($client);

        $this->assertEquals('/accounts', $apiResource->getBaseUri());
        $this->assertInstanceOf(Client\Credentials\Handler\BasicHandler::class, $apiResource->getAuthHandlers()[0]);
        $this->assertEquals('secrets', $apiResource->getCollectionName());
    }
}
