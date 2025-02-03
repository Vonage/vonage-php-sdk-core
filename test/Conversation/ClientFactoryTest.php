<?php

declare(strict_types=1);

namespace VonageTest\Conversation;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Factory\MapFactory;
use Vonage\Conversation\ClientFactory;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithConfiguredApiResource(): void
    {
        $mockServices = [
            'conversation' => ClientFactory::class,
            APIResource::class => APIResource::class,
        ];

        $mockClient = $this->createMock(Client::class);
        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $result = $factory($container);
        $this->assertInstanceOf(\Vonage\Conversation\Client::class, $result);
        $this->assertEquals('https://api.nexmo.com/v1/conversations', $result->getAPIResource()->getBaseUrl());
        $this->assertInstanceOf(Client\Credentials\Handler\KeypairHandler::class, $result->getAPIResource()
            ->getAuthHandlers()[0]);
        $this->assertFalse($result->getAPIResource()->errorsOn200());
        $this->assertTrue($result->getAPIResource()->isHAL());
    }
}
