<?php

declare(strict_types=1);

namespace Vonage\Client;

use RuntimeException;
use Vonage\Client;

trait ClientAwareTrait
{
    /**
     * @var Client
     */
    protected $client;

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getClient(): ?Client
    {
        if (isset($this->client)) {
            return $this->client;
        }

        throw new RuntimeException('Vonage\Client not set');
    }
}
