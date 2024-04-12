<?php

declare(strict_types=1);

namespace Vonage\Client\Factory;

/**
 * Interface FactoryInterface
 *
 * Factor create API clients (clients specific to single API, that leverages Vonage\Client for HTTP communication and
 * common functionality).
 */
interface FactoryInterface
{
    public function hasApi(string $api): bool;

    public function getApi(string $api);

    public function make(string $key);
}
