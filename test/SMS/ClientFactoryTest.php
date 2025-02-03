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
        $mockServices = [
            'sms' => ClientFactory::class,
            APIResource::class => APIResource::class,
        ];

        $mockClient = $this->createMock(Client::class);
        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $result = $factory($container);
        $this->assertInstanceOf(\Vonage\Sms\Client::class, $result);
        $this->assertInstanceOf(Client\Credentials\Handler\BasicHandler::class, $result->getAPIResource()
            ->getAuthHandlers()[0]);
        $this->assertInstanceOf(Client\Credentials\Handler\SignatureBodyHandler::class, $result->getAPIResource()
            ->getAuthHandlers()[1]);
        $this->assertFalse($result->getAPIResource()->isHAL());
        $this->assertTrue($result->getAPIResource()->errorsOn200());
        $this->assertEquals('messages', $result->getAPIResource()->getCollectionName());
    }
}
