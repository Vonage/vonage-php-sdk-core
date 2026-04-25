<?php

declare(strict_types=1);

namespace VonageTest\Account;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Factory\MapFactory;
use Vonage\Account\ClientFactory;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithConfiguredApiResource(): void
    {
        $mockClient = $this->createMock(Client::class);

        $mockServices = [
            'account' => ClientFactory::class,
            APIResource::class => APIResource::class,
        ];

        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $result = $factory($container);

        $reflection = new \ReflectionClass($result);
        $apiProperty = $reflection->getProperty('accountAPI');
        $api = $apiProperty->getValue($result);

        $this->assertInstanceOf(\Vonage\Account\Client::class, $result);
        $this->assertEquals('/account', $api->getBaseUri());
        $this->assertInstanceOf(Client\Credentials\Handler\BasicHandler::class, $api->getAuthHandlers()[0]);
        $this->assertFalse($api->isHAL());
    }
}
