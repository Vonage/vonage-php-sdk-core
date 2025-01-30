<?php

declare(strict_types=1);

namespace VonageTest\Insights;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Factory\MapFactory;
use Vonage\Insights\ClientFactory;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithConfiguredApiResource(): void
    {
        $mockServices = [
            'insights' => ClientFactory::class,
            APIResource::class => APIResource::class,
        ];

        $mockClient = $this->createMock(Client::class);
        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $result = $factory($container);
        $this->assertInstanceOf(\Vonage\Insights\Client::class, $result);
        $this->assertInstanceOf(Client\Credentials\Handler\BasicHandler::class, $result->getAPIResource()
            ->getAuthHandlers()[0]);
        $this->assertFalse($result->getAPIResource()->isHAL());
    }
}
