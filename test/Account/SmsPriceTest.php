<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Account;

use Vonage\Network;
use Vonage\Account\SmsPrice;
use PHPUnit\Framework\TestCase;

class SmsPriceTest extends TestCase
{
    public function setUp(): void
    {
    }

    /**
     * @dataProvider smsPriceProvider
     */
    public function testFromArray($smsPrice)
    {
        $this->assertEquals("US", $smsPrice->getCountryCode());
        $this->assertEquals("United States", $smsPrice->getCountryName());
        $this->assertEquals("1", $smsPrice->getDialingPrefix());
        $this->assertEquals("0.00512", $smsPrice->getDefaultPrice());
    }

    /**
     * @dataProvider smsPriceProvider
     */
    public function testGetters($smsPrice)
    {
        $this->assertEquals("US", $smsPrice->getCountryCode());
        $this->assertEquals("United States", $smsPrice->getCountryName());
        $this->assertEquals("United States", $smsPrice->getCountryDisplayName());
        $this->assertEquals("1", $smsPrice->getDialingPrefix());
        $this->assertEquals("0.00512", $smsPrice->getDefaultPrice());
    }

    /**
     * @dataProvider smsPriceProvider
     */
    public function testArrayAccess($smsPrice)
    {
        $this->assertEquals("US", @$smsPrice['country_code']);
        $this->assertEquals("United States", @$smsPrice['country_name']);
        $this->assertEquals("United States", @$smsPrice['country_display_name']);
        $this->assertEquals("1", @$smsPrice['dialing_prefix']);
        $this->assertEquals("0.00512", @$smsPrice['default_price']);
    }

    /**
     * @dataProvider smsPriceProvider
     */
    public function testUsesCustomPriceForKnownNetwork($smsPrice)
    {
        $this->assertEquals("0.123", $smsPrice->getPriceForNetwork('21039'));
    }

    /**
     * @dataProvider smsPriceProvider
     */
    public function testUsesDefaultPriceForUnknownNetwork($smsPrice)
    {
        $this->assertEquals("0.00512", $smsPrice->getPriceForNetwork('007'));
    }

    public function smsPriceProvider()
    {
        $r = [];

        $smsPrice = new SmsPrice();
        @$smsPrice->fromArray([
            'dialing_prefix' => 1,
            'default_price' => '0.00512',
            'currency' => 'EUR',
            'country_code' => 'US',
            'country_name' => 'United States',
            'country_display_name' => 'United States',
            'prefix' => 1,
            'networks' => [
                [
                    'currency' => 'EUR',
                    'networkCode' => '21039',
                    'networkName' => 'Demo Network',
                    'price' => '0.123'
                ]
            ]
        ]);
        $r['jsonUnserialize'] = [$smsPrice];

        return $r;
    }
}
