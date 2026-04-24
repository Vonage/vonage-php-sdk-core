<?php

declare(strict_types=1);

namespace VonageTest\Verify;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\APIResourceFactory;
use Vonage\Client\Factory\MapFactory;
use Vonage\Verify\ClientFactory;
use Vonage\Verify\ExceptionErrorHandler;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithConfiguredApiResource(): void
    {
        $mockClient = $this->createMock(Client::class);

        $mockServices = [
            'verify' => ClientFactory::class,
            APIResource::class => APIResourceFactory::class,
            Client::class => fn() => $mockClient,
        ];

        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $result = $factory($container);
        $this->assertInstanceOf(\Vonage\Verify\Client::class, $result);

        $reflection = new \ReflectionClass($result);
        $apiProperty = $reflection->getProperty('api');
        $apiResource = $apiProperty->getValue($result);

        $this->assertInstanceOf(Client\Credentials\Handler\BasicHandler::class, $apiResource->getAuthHandlers()[0]);
        $this->assertEquals('/verify', $apiResource->getBaseUri());
        $this->assertTrue($apiResource->errorsOn200());
        $this->assertInstanceOf(ExceptionErrorHandler::class, $apiResource->getExceptionErrorHandler());
    }
}
