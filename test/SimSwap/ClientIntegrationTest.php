<?php

declare(strict_types=1);

namespace VonageTest\SimSwap;

use VonageTest\VonageTestCase;
use Vonage\Client;

/**
 * SimSwap requires a configuration test because GNP setup is more complex than other clients
 */
class ClientIntegrationTest extends VonageTestCase
{
    protected string|false $key;

    public function setUp(): void
    {
        $this->key = file_get_contents(__DIR__ . '/../Client/Credentials/test.key');

        parent::setUp();
    }

    public function testWillConfigureClientCorrectly(): void
    {
        $vonageClient = new Client(new Client\Credentials\Gnp('999', $this->key, 'XXX'));
        $simSwapClient = $vonageClient->simswap();
        $authHandlers = $simSwapClient->getAPIResource()->getAuthHandlers();
        $this->assertInstanceOf(\Vonage\SimSwap\Client::class, $simSwapClient);

        // Assert that Auth handler is Gnp
        $this->assertInstanceOf(Client\Credentials\Handler\SimSwapGnpHandler::class, $authHandlers[0]);

        // Assert that Auth handler has a configured client with Gnp credentials
        /** @var Client $handlerClient */
        $handlerClient = $authHandlers[0]->getClient();
        $this->assertInstanceOf(Client\Credentials\Gnp::class, $handlerClient->getCredentials());

        $this->assertTrue(true);
    }
}
