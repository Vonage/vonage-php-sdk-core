<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Client\Factory;

use Vonage\Client;

class TestDouble implements Client\ClientAwareInterface
{
    /**
     * @var Client
     */
    public $client;

    public function setClient(Client $client)
    {
        $this->client = $client;
    }


}