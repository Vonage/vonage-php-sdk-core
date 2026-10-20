<?php

declare(strict_types=1);

namespace VonageTest\Client\Factory;

use Vonage\Client;

class TestDouble
{
    public function __construct(public Client $client) {
    }
}
