<?php

declare(strict_types=1);

namespace VonageTest\Verify2;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Factory\MapFactory;
use Vonage\Verify2\ClientFactory;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithConfiguredApiResource(): void
    {
        $mockServices = [
            'verify2' => ClientFactory::class,
            APIResource::class => APIResource::class,
        ];

        $mockClient = $this->createMock(Client::class);
        $mockClient->method('getApiUrl')->willReturn('https://api.nexmo.com');
        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $result = $factory($container);
        $this->assertInstanceOf(\Vonage\Verify2\Client::class, $result);
        $this->assertInstanceOf(Client\Credentials\Handler\KeypairHandler::class, $result->getAPIResource()
            ->getAuthHandlers()[0]);
        $this->assertInstanceOf(Client\Credentials\Handler\BasicHandler::class, $result->getAPIResource()
            ->getAuthHandlers()[1]);
        $this->assertEquals('https://api.nexmo.com', $result->getAPIResource()->getBaseUrl());
        $this->assertEquals('/v2/verify', $result->getAPIResource()->getBaseUri());
        $this->assertFalse($result->getApiResource()->errorsOn200());
        $this->assertFalse($result->getApiResource()->isHAL());
    }
}
