<?php

declare(strict_types=1);

namespace VonageTest\Meetings;

use PHPUnit\Framework\Error\Deprecated;
use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Factory\MapFactory;
use Vonage\Meetings\ClientFactory;
use Vonage\Meetings\ExceptionErrorHandler;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithConfiguredApiResource(): void
    {
        $mockServices = [
            'meetings' => ClientFactory::class,
            APIResource::class => APIResource::class,
        ];

        $mockClient = $this->createMock(Client::class);
        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $result = @$factory($container);
        $this->assertInstanceOf(\Vonage\Meetings\Client::class, $result);
        $this->assertInstanceOf(Client\Credentials\Handler\KeypairHandler::class, $result->getAPIResource()
            ->getAuthHandlers()[0]);
        $this->assertEquals('https://api-eu.vonage.com/v1/meetings/', $result->getAPIResource()->getBaseUrl());
        $this->assertInstanceOf(ExceptionErrorHandler::class, $result->getAPIResource()->getExceptionErrorHandler());
    }
}
