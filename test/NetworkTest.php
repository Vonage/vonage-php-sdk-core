<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest;

use Vonage\Network;
use PHPUnit\Framework\TestCase;

class NetworkTest extends TestCase
{
    public function testNetworkArrayAccess()
    {
        $network = new Network('12345', 'Demo Network');
        $this->assertEquals(@$network['network_code'], '12345');
        $this->assertEquals(@$network['network_name'], 'Demo Network');
    }

    public function testNetworkGetters()
    {
        $network = new Network('12345', 'Demo Network');
        $this->assertEquals($network->getCode(), '12345');
        $this->assertEquals($network->getName(), 'Demo Network');
    }

    public function testNetworkFromArray()
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

        $this->assertEquals($network->getCode(), '12345');
        $this->assertEquals($network->getName(), 'Demo Network');
        $this->assertEquals($network->getOutboundSmsPrice(), '0.0331');
        $this->assertEquals($network->getOutboundVoicePrice(), '0.0123');
        $this->assertEquals($network->getCurrency(), 'EUR');
    }

    public function testSmsPriceFallback()
    {
        $network = new Network('12345', 'Demo Network');
        $network->fromArray([
            'price' => '0.0331',
        ]);

        $this->assertEquals($network->getOutboundSmsPrice(), '0.0331');
    }

    public function testVoicePriceFallback()
    {
        $network = new Network('12345', 'Demo Network');
        $network->fromArray([
            'price' => '0.0331',
        ]);

        $this->assertEquals($network->getOutboundSmsPrice(), '0.0331');
    }
}
