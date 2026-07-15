<?php

declare(strict_types=1);

namespace VonageTest\Messages;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Factory\MapFactory;
use Vonage\Messages\ClientFactory;
use Vonage\Messages\ExceptionErrorHandler;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithConfiguredApiResource(): void
    {
        $mockClient = $this->createMock(Client::class);

        $mockServices = [
            'messages' => ClientFactory::class,
            APIResource::class => APIResource::class,
        ];

        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $result = $factory($container);
        $this->assertInstanceOf(\Vonage\Messages\Client::class, $result);

        $reflection = new \ReflectionClass($result);
        $apiProperty = $reflection->getProperty('api');
        $apiResource = $apiProperty->getValue($result);

        $this->assertInstanceOf(Client\Credentials\Handler\KeypairHandler::class, $apiResource->getAuthHandlers()[0]);
        $this->assertInstanceOf(Client\Credentials\Handler\BasicHandler::class, $apiResource->getAuthHandlers()[1]);
        $this->assertEquals('/v1/messages', $apiResource->getBaseUrl());
        $this->assertInstanceOf(ExceptionErrorHandler::class, $apiResource->getExceptionErrorHandler());
        $this->assertFalse($apiResource->isHAL());
        $this->assertFalse($apiResource->errorsOn200());
    }
}
