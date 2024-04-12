<?php

declare(strict_types=1);

namespace VonageTest\Client\Factory;

use Vonage\Client;

class TestDouble implements Client\ClientAwareInterface
{
    /**
     * @var Client
     */
    public $client;

    public function setClient(Client $client): void
    {
        $this->client = $client;
    }
}
