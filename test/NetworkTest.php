<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest;

use PHPUnit\Framework\TestCase;
use Vonage\Network;

class NetworkTest extends TestCase
{
    public function testNetworkArrayAccess(): void
    {
        $network = new Network('12345', 'Demo Network');

        self::assertEquals('12345', $network['network_code']);
        self::assertEquals('Demo Network', $network['network_name']);
    }

    public function testNetworkGetters(): void
    {
        $network = new Network('12345', 'Demo Network');

        self::assertEquals('12345', $network->getCode());
        self::assertEquals('Demo Network', $network->getName());
    }

    public function testNetworkFromArray(): void
    {
        $network = new Network('12345', 'Demo Network');
        $network->fromArray([
            'type' => 'mobile',
            'networkCode' => '12345',
            'networkName' => 'Demo Network',
            'sms_price' => '0.0331',
            'voice_price' => '0.0123',
            'currency' => 'EUR',
            'mcc' => '310',
            'mnc' => '740',
        ]);

        self::assertEquals('12345', $network->getCode());
        self::assertEquals('Demo Network', $network->getName());
        self::assertEquals('0.0331', $network->getOutboundSmsPrice());
        self::assertEquals('0.0123', $network->getOutboundVoicePrice());
        self::assertEquals('EUR', $network->getCurrency());
    }

    public function testSmsPriceFallback(): void
    {
        $network = new Network('12345', 'Demo Network');
        $network->fromArray(['price' => '0.0331']);

        self::assertEquals('0.0331', $network->getOutboundSmsPrice());
    }

    public function testVoicePriceFallback(): void
    {
        $network = new Network('12345', 'Demo Network');
        $network->fromArray(['price' => '0.0331']);

        self::assertEquals('0.0331', $network->getOutboundSmsPrice());
    }
}
