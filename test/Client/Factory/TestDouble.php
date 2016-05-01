<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Client\Factory;

use Nexmo\Client;

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