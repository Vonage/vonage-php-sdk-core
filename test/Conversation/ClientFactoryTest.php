<?php

declare(strict_types=1);

namespace VonageTest\Conversation;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\APIResourceFactory;
use Vonage\Client\Factory\MapFactory;
use Vonage\Conversation\ClientFactory;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithConfiguredApiResource(): void
    {
        $mockClient = $this->createMock(Client::class);

        $mockServices = [
            'conversation' => ClientFactory::class,
            APIResource::class => APIResourceFactory::class,
            Client::class => fn() => $mockClient,
        ];

        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $result = $factory($container);
        $this->assertInstanceOf(\Vonage\Conversation\Client::class, $result);

        $reflection = new \ReflectionClass($result);
        $apiProperty = $reflection->getProperty('api');
        $apiResource = $apiProperty->getValue($result);

        $this->assertEquals('https://api.nexmo.com/v1/conversations', $apiResource->getBaseUrl());
        $this->assertInstanceOf(Client\Credentials\Handler\KeypairHandler::class, $apiResource->getAuthHandlers()[0]);
        $this->assertFalse($apiResource->errorsOn200());
        $this->assertTrue($apiResource->isHAL());
    }
}
