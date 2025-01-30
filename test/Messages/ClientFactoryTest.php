<?php

declare(strict_types=1);

namespace VonageTest\Messages;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Factory\MapFactory;
use Vonage\Messages\ClientFactory;
use Vonage\Messages\ExceptionErrorHandler;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithConfiguredApiResource(): void
    {
        $mockServices = [
            'messages' => ClientFactory::class,
            APIResource::class => APIResource::class,
        ];

        $mockClient = $this->createMock(Client::class);
        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $result = $factory($container);
        $this->assertInstanceOf(\Vonage\Messages\Client::class, $result);
        $this->assertInstanceOf(Client\Credentials\Handler\KeypairHandler::class, $result->getAPIResource()
            ->getAuthHandlers()[0]);
        $this->assertInstanceOf(Client\Credentials\Handler\BasicHandler::class, $result->getAPIResource()
            ->getAuthHandlers()[1]);
        $this->assertEquals('/v1/messages', $result->getAPIResource()->getBaseUrl());
        $this->assertInstanceOf(ExceptionErrorHandler::class, $result->getAPIResource()->getExceptionErrorHandler());
        $this->assertFalse($result->getAPIResource()->isHAL());
        $this->assertFalse($result->getAPIResource()->errorsOn200());
    }
}
