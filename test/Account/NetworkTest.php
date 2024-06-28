<?php

namespace VonageTest\Account;

use Vonage\Account\Network;
use VonageTest\VonageTestCase;

class NetworkTest extends VonageTestCase
{
    public function testNetworkGetters(): void
    {
        $network = new Network('12345', 'Demo Network');

        $this->assertEquals('12345', $network->getCode());
        $this->assertEquals('Demo Network', $network->getName());
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

        $this->assertEquals('12345', $network->getCode());
        $this->assertEquals('Demo Network', $network->getName());
        $this->assertEquals('0.0331', $network->getOutboundSmsPrice());
        $this->assertEquals('0.0123', $network->getOutboundVoicePrice());
        $this->assertEquals('EUR', $network->getCurrency());
    }

    public function testSmsPriceFallback(): void
    {
        $network = new Network('12345', 'Demo Network');
        $network->fromArray(['price' => '0.0331']);

        $this->assertEquals('0.0331', $network->getOutboundSmsPrice());
    }

    public function testVoicePriceFallback(): void
    {
        $network = new Network('12345', 'Demo Network');
        $network->fromArray(['price' => '0.0331']);

        $this->assertEquals('0.0331', $network->getOutboundSmsPrice());
    }
}
