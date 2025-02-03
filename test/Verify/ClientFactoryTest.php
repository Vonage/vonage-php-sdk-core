<?php

declare(strict_types=1);

namespace VonageTest\Verify;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Factory\MapFactory;
use Vonage\Verify\ClientFactory;
use Vonage\Verify\ExceptionErrorHandler;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithConfiguredApiResource(): void
    {
        $mockServices = [
            'verify' => ClientFactory::class,
            APIResource::class => APIResource::class,
        ];

        $mockClient = $this->createMock(Client::class);
        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $result = $factory($container);
        $this->assertInstanceOf(\Vonage\Verify\Client::class, $result);
        $this->assertInstanceOf(Client\Credentials\Handler\TokenBodyHandler::class, $result->getAPIResource()
            ->getAuthHandlers()[0]);
        $this->assertEquals('/verify', $result->getAPIResource()->getBaseUri());
        $this->assertTrue($result->getApiResource()->errorsOn200());
        $this->assertInstanceOf(ExceptionErrorHandler::class, $result->getAPIResource()->getExceptionErrorHandler());
    }
}
