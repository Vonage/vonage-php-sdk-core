<?php

declare(strict_types=1);

namespace VonageTest\Secrets;

use PHPUnit\Framework\TestCase;
use Vonage\Secrets\ClientFactory;
use Vonage\Secrets\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\BasicHandler;
use Vonage\Client\Factory\FactoryInterface;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithProperConfiguration(): void
    {
        // Mock the FactoryInterface
        $factoryMock = $this->createMock(FactoryInterface::class);

        // Mock the APIResource
        $apiResourceMock = $this->createMock(APIResource::class);

        // Configure the factory to return the mocked APIResource
        $factoryMock->expects($this->once())
            ->method('make')
            ->with(APIResource::class)
            ->willReturn($apiResourceMock);

        // Expect the methods on APIResource to be called with specific parameters
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

        // Create an instance of the ClientFactory
        $clientFactory = new ClientFactory();

        // Call the __invoke method and retrieve the Client
        $client = $clientFactory($factoryMock);

        // Assert that the result is an instance of the Client
        $this->assertInstanceOf(Client::class, $client);

        // Assert that the Client has the correctly configured APIResource (optional, if Client exposes it)
         $this->assertSame($apiResourceMock, $client->getApiResource());
    }
}
