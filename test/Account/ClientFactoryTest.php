<?php
declare(strict_types=1);

namespace NexmoTest\Account;

use Vonage\Account\Client;
use Vonage\Client\APIResource;
use PHPUnit\Framework\TestCase;
use Vonage\Account\ClientFactory;
use Vonage\Client\Factory\MapFactory;

class ClientFactoryTest extends TestCase
{
    /**
     * @var MapFactory
     */
    protected $mapFactory;

    /**
     * @var Client
     */
    protected $vonageClient;

    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize('Vonage\Client');
        $this->vonageClient->getRestUrl()->willReturn('https://rest.nexmo.com');
        $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');

        $this->mapFactory = new MapFactory([
            APIResource::class => APIResource::class
        ], $this->vonageClient->reveal());
    }
    
    public function testURIsAreCorrect()
    {
        $factory = new ClientFactory();
        /** @var Client $client */
        $client = $factory($this->mapFactory);

        $this->assertSame('/accounts', $client->getSecretsAPI()->getBaseUri());
        $this->assertSame('https://api.nexmo.com', $client->getSecretsAPI()->getBaseUrl());

        $this->assertSame('/account', $client->getAccountAPI()->getBaseUri());
        $this->assertSame('https://rest.nexmo.com', $client->getAccountAPI()->getBaseUrl());
    }
}
