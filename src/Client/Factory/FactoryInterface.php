<?php

declare(strict_types=1);

namespace Vonage\Client\Factory;

use Psr\Container\ContainerInterface;

/**
 * Interface FactoryInterface
 *
 * Factor create API clients (clients specific to single API, that leverages Vonage\Client for HTTP communication and
 * common functionality).
 */
interface FactoryInterface extends ContainerInterface
{
    public function make(string $key);
}
