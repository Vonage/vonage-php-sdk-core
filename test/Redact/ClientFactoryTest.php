<?php

declare(strict_types=1);

namespace VonageTest\Redact;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\APIResourceFactory;
use Vonage\Client\Factory\MapFactory;
use Vonage\Redact\ClientFactory;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithConfiguredApiResource(): void
    {
        $mockClient = $this->createMock(Client::class);

        $mockServices = [
            'redact' => ClientFactory::class,
            APIResource::class => APIResourceFactory::class,
            Client::class => fn() => $mockClient,
        ];

        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $result = $factory($container);

        $reflection = new \ReflectionClass($result);
        $apiProperty = $reflection->getProperty('api');
        $api = $apiProperty->getValue($result);

        $this->assertInstanceOf(\Vonage\Redact\Client::class, $result);
        $this->assertInstanceOf(Client\Credentials\Handler\BasicHandler::class, $api->getAuthHandlers()[0]);
        $this->assertEquals('%s - %s. See %s for more information', $api->getExceptionErrorHandler()->getRfc7807Format());
    }
}
