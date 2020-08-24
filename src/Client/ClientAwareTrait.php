<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Client;

use Vonage\Client;

trait ClientAwareTrait
{
    /**
     * @var Client
     */
    protected $client;

    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    public function getClient()
    {
        if (isset($this->client)) {
            return $this->client;
        }

        throw new \RuntimeException('Vonage\Client not set');
    }
}
