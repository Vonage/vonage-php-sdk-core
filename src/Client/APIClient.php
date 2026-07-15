<?php

declare(strict_types=1);

namespace Vonage\Client;

/**
 * @deprecated This interface is being removed in the next major version. Each API client will no longer
 *             expose a getAPIResource() method. Do not type-hint to this interface.
 */
interface APIClient
{
    public function getAPIResource(): APIResource;
}
