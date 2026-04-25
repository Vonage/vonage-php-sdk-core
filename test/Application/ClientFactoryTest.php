<?php

declare(strict_types=1);

namespace VonageTest\Application;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Factory\MapFactory;
use Vonage\Application\ClientFactory;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithConfiguredApiResource(): void
    {
        $mockClient = $this->createMock(Client::class);

        $mockServices = [
            'application' => ClientFactory::class,
            APIResource::class => APIResource::class,
        ];

        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $result = $factory($container);

        $reflection = new \ReflectionClass($result);
        $apiProperty = $reflection->getProperty('api');
        $api = $apiProperty->getValue($result);

        $this->assertInstanceOf(\Vonage\Application\Client::class, $result);
        $this->assertEquals('/v2/applications', $api->getBaseUri());
        $this->assertInstanceOf(Client\Credentials\Handler\BasicHandler::class, $api->getAuthHandlers()[0]);
        $this->assertEquals('applications', $api->getCollectionName());
    }
}
