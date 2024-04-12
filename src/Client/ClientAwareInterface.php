<?php

declare(strict_types=1);

namespace Vonage\Client;

use Vonage\Client;

interface ClientAwareInterface
{
    public function setClient(Client $client);
}
