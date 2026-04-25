<?php

declare(strict_types=1);

namespace Vonage\Client;

use Vonage\Client;

/**
 * @deprecated Inject Vonage\Client (or APIResource) via constructor instead.
 *             This interface and setClient() / getClient() will be removed in the next major version.
 */
interface ClientAwareInterface
{
    public function setClient(Client $client);
}
