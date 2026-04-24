<?php

declare(strict_types=1);

namespace VonageTest\Numbers;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\APIResourceFactory;
use Vonage\Client\Factory\MapFactory;
use Vonage\Entity\Hydrator\ArrayHydrator;
use Vonage\Numbers\ClientFactory;
use Vonage\Numbers\Number;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithConfiguredApiResource(): void
    {
        $mockClient = $this->createMock(Client::class);

        $mockServices = [
            'numbers' => ClientFactory::class,
            APIResource::class => APIResourceFactory::class,
            Client::class => fn() => $mockClient,
        ];

        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $result = $factory($container);
        $this->assertInstanceOf(\Vonage\Numbers\Client::class, $result);

        $reflection = new \ReflectionClass($result);
        $apiProperty = $reflection->getProperty('api');
        $apiResource = $apiProperty->getValue($result);

        $this->assertInstanceOf(Client\Credentials\Handler\BasicHandler::class, $apiResource->getAuthHandlers()[0]);
        $this->assertFalse($apiResource->isHAL());

        $hydratorProperty = $reflection->getProperty('hydrator');
        $hydrator = $hydratorProperty->getValue($result);
        $this->assertInstanceOf(ArrayHydrator::class, $hydrator);

        $prototypeProperty = (new \ReflectionClass($hydrator))->getProperty('prototype');
        $this->assertInstanceOf(Number::class, $prototypeProperty->getValue($hydrator));
    }
}
