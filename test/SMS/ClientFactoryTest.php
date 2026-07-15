<?php

declare(strict_types=1);

namespace VonageTest\SMS;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Factory\MapFactory;
use Vonage\SMS\ClientFactory;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithConfiguredApiResource(): void
    {
        $mockClient = $this->createMock(Client::class);

        $mockServices = [
            'sms' => ClientFactory::class,
            APIResource::class => APIResource::class,
        ];

        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $result = $factory($container);
        $this->assertInstanceOf(\Vonage\Sms\Client::class, $result);

        $reflection = new \ReflectionClass($result);
        $apiProperty = $reflection->getProperty('api');
        $apiResource = $apiProperty->getValue($result);

        $this->assertInstanceOf(Client\Credentials\Handler\BasicHandler::class, $apiResource->getAuthHandlers()[0]);
        $this->assertInstanceOf(Client\Credentials\Handler\SignatureBodyHandler::class, $apiResource->getAuthHandlers()[1]);
        $this->assertFalse($apiResource->isHAL());
        $this->assertTrue($apiResource->errorsOn200());
        $this->assertEquals('messages', $apiResource->getCollectionName());
    }
}
