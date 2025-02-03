<?php

declare(strict_types=1);

namespace VonageTest\SimSwap;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Factory\MapFactory;
use Vonage\SimSwap\ClientFactory;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithConfiguredApiResource(): void
    {
        $mockClient = $this->createMock(Client::class);

        $mockServices = [
            'simSwap' => ClientFactory::class,
            APIResource::class => APIResource::class,
            Client::class => fn () => $mockClient,
        ];

        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $result = $factory($container);
        $this->assertInstanceOf(\Vonage\SimSwap\Client::class, $result);
        $this->assertInstanceOf(Client\Credentials\Handler\SimSwapGnpHandler::class, $result->getAPIResource()
            ->getAuthHandlers()[0]);
        $this->assertFalse($result->getAPIResource()->isHAL());
        $this->assertFalse($result->getAPIResource()->errorsOn200());
        $this->assertEquals('https://api-eu.vonage.com/camara/sim-swap/v040', $result->getAPIResource()
            ->getBaseUrl());
        $this->assertEquals('https://api-eu.vonage.com/oauth2/bc-authorize', $result->getAPIResource()->getAuthHandlers()[0]->getBaseUrl());
        $this->assertEquals('https://api-eu.vonage.com/oauth2/token', $result->getAPIResource()->getAuthHandlers()
        [0]->getTokenUrl());
    }
}
