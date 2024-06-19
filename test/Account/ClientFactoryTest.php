<?php

declare(strict_types=1);

namespace VonageTest\Account;

use Hoa\Iterator\Map;
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
        // Configure a base HTTPClient Object
        $httpClient = new \Vonage\Client\HttpClient();

        $this->mapFactory = new MapFactory([
            APIResource::class => APIResource::class,
            'credentials' => new Client\Credentials\Basic('xxx', 'yyy'),
            \Vonage\Client\HttpClient::class => $httpClient,
        ], $this->prophesize(Client::class)->reveal());
    }

    public function testURIsAreCorrect(): void
    {
        $factory = new ClientFactory();
        $client = $factory($this->mapFactory);

        $this->assertSame('/account', $client->getAPIResource()->getBaseUri());
        $this->assertSame('https://rest.nexmo.com', $client->getAPIResource()->getBaseUrl());
    }
}
