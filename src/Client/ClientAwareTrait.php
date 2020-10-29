<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

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
