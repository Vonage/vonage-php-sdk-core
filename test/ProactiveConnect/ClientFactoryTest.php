<?php

declare(strict_types=1);

namespace VonageTest\ProactiveConnect;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Factory\MapFactory;
use Vonage\ProactiveConnect\ClientFactory;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithConfiguredApiResource(): void
    {
        $mockServices = [
            'proactiveConnect' => ClientFactory::class,
            APIResource::class => APIResource::class,
        ];

        $mockClient = $this->createMock(Client::class);
        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $result = $factory($container);
        $this->assertInstanceOf(\Vonage\ProactiveConnect\Client::class, $result);
        $this->assertInstanceOf(Client\Credentials\Handler\KeypairHandler::class, $result->getAPIResource()
            ->getAuthHandlers()[0]);
        $this->assertEquals('https://api-eu.vonage.com/v0.1/bulk/', $result->getAPIResource()->getBaseUrl());
        $this->assertFalse($result->getAPIResource()->isHAL());
        $this->assertFalse($result->getAPIResource()->errorsOn200());
    }
}
