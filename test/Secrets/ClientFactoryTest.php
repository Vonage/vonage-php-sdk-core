<?php

declare(strict_types=1);

namespace VonageTest\Secrets;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\BasicHandler;
use Vonage\Client\Factory\FactoryInterface;
use Vonage\Secrets\Client as SecretsClient;
use Vonage\Secrets\ClientFactory;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithProperConfiguration(): void
    {
        $factoryMock = $this->createMock(FactoryInterface::class);
        $apiResourceMock = $this->createMock(APIResource::class);

        $factoryMock->expects($this->once())
            ->method('make')
            ->with(APIResource::class)
            ->willReturn($apiResourceMock);

        $apiResourceMock->expects($this->once())
            ->method('setBaseUri')
            ->with('/accounts')
            ->willReturnSelf();

        $apiResourceMock->expects($this->once())
            ->method('setAuthHandlers')
            ->with($this->isInstanceOf(BasicHandler::class))
            ->willReturnSelf();

        $apiResourceMock->expects($this->once())
            ->method('setCollectionName')
            ->with('secrets')
            ->willReturnSelf();

        $clientFactory = new ClientFactory();
        $client = $clientFactory($factoryMock);

        $this->assertInstanceOf(SecretsClient::class, $client);

        $reflection = new \ReflectionClass($client);
        $apiProperty = $reflection->getProperty('api');
        $this->assertSame($apiResourceMock, $apiProperty->getValue($client));
    }
}
