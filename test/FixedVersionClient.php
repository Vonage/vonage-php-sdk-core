<?php

declare(strict_types=1);

namespace VonageTest;

use Vonage\Client;

class FixedVersionClient extends Client
{
    public function getVersion(): string
    {
        return '1.2.3';
    }
}
