<?php

declare(strict_types=1);

namespace VonageTest\Account;

use VonageTest\VonageTestCase;
use Vonage\Account\ClientFactory;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Factory\MapFactory;

class ClientFactoryTest extends VonageTestCase
{
    /**
     * @var MapFactory
     */
    protected $mapFactory;

    protected $vonageClient;

    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getRestUrl()->willReturn('https://rest.nexmo.com');
        $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');

        /** @noinspection PhpParamsInspection */
        $this->mapFactory = new MapFactory([APIResource::class => APIResource::class], $this->vonageClient->reveal());
    }

    public function testURIsAreCorrect(): void
    {
        $factory = new ClientFactory();
        $client = $factory($this->mapFactory);

        $this->assertSame('/account', $client->getAPIResource()->getBaseUri());
        $this->assertSame('https://rest.nexmo.com', $client->getAPIResource()->getBaseUrl());
    }
}
