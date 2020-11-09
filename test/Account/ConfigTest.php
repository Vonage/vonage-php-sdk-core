<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Account;

use PHPUnit\Framework\TestCase;
use Vonage\Account\Config;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    private $config;

    public function setUp(): void
    {
        $this->config = new Config(
            "https://example.com/webhooks/inbound-sms",
            "https://example.com/webhooks/delivery-receipt",
            30, // different values so we can check if we reversed one anywhere
            31,
            32
        );
    }

    public function testObjectAccess(): void
    {
        $this->assertEquals("https://example.com/webhooks/inbound-sms", $this->config->getSmsCallbackUrl());
        $this->assertEquals("https://example.com/webhooks/delivery-receipt", $this->config->getDrCallbackUrl());
        $this->assertEquals(30, $this->config->getMaxOutboundRequest());
        $this->assertEquals(31, $this->config->getMaxInboundRequest());
        $this->assertEquals(32, $this->config->getMaxCallsPerSecond());
    }

    public function testArrayAccess(): void
    {
        $this->assertEquals("https://example.com/webhooks/inbound-sms", @$this->config['sms_callback_url']);
        $this->assertEquals("https://example.com/webhooks/delivery-receipt", @$this->config['dr_callback_url']);
        $this->assertEquals(30, @$this->config['max_outbound_request']);
        $this->assertEquals(31, @$this->config['max_inbound_request']);
        $this->assertEquals(32, @$this->config['max_calls_per_second']);
    }
}
