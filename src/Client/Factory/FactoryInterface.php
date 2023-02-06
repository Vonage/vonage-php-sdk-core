<?php

declare(strict_types=1);

namespace Vonage\Client\Factory;

/**
 * Interface FactoryInterface
 *
 * Factor create API clients (clients specific to single API, that leverages Vonage\Client for
 * common functionality).
 */
interface FactoryInterface
{
    public function make(string $key);
}
