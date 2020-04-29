<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2019 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Account;

use Nexmo\Account\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function setUp()
    {
        $this->config = new Config(
            "https://example.com/webhooks/inbound-sms",
            "https://example.com/webhooks/delivery-receipt",
            30, // different values so we can check if we reversed one anywhere
            31,
            32
        );
    }

    public function testObjectAccess()
    {
        $this->assertEquals("https://example.com/webhooks/inbound-sms", $this->config->getSmsCallbackUrl());
        $this->assertEquals("https://example.com/webhooks/delivery-receipt", $this->config->getDrCallbackUrl());
        $this->assertEquals(30, $this->config->getMaxOutboundRequest());
        $this->assertEquals(31, $this->config->getMaxInboundRequest());
        $this->assertEquals(32, $this->config->getMaxCallsPerSecond());
    }
}
