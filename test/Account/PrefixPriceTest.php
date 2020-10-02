<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Account;

use Vonage\Network;
use Vonage\Account\PrefixPrice;
use PHPUnit\Framework\TestCase;

class PrefixPriceTest extends TestCase
{
    public function setUp(): void
    {
    }

    /**
     * @dataProvider prefixPriceProvider
     */
    public function testFromArray($prefixPrice)
    {
        $this->assertEquals("ZW", $prefixPrice->getCountryCode());
        $this->assertEquals("Zimbabwe", $prefixPrice->getCountryName());
        $this->assertEquals("263", $prefixPrice->getDialingPrefix());
    }

    /**
     * @dataProvider prefixPriceProvider
     */
    public function testGetters($prefixPrice)
    {
        $this->assertEquals("ZW", $prefixPrice->getCountryCode());
        $this->assertEquals("Zimbabwe", $prefixPrice->getCountryName());
        $this->assertEquals("Zimbabwe", $prefixPrice->getCountryDisplayName());
        $this->assertEquals("263", $prefixPrice->getDialingPrefix());
    }

    /**
     * @dataProvider prefixPriceProvider
     */
    public function testArrayAccess($prefixPrice)
    {
        $this->assertEquals("ZW", @$prefixPrice['country_code']);
        $this->assertEquals("Zimbabwe", @$prefixPrice['country_name']);
        $this->assertEquals("Zimbabwe", @$prefixPrice['country_display_name']);
        $this->assertEquals("263", @$prefixPrice['dialing_prefix']);
    }

    /**
     * @dataProvider prefixPriceProvider
     */
    public function testUsesCustomPriceForKnownNetwork($prefixPrice)
    {
        $this->assertEquals("0.123", $prefixPrice->getPriceForNetwork('21039'));
    }

    public function prefixPriceProvider()
    {
        $r = [];

        $prefixPrice = new PrefixPrice();
        @$prefixPrice->fromArray([
            'country' => 'ZW',
            'name' => 'Zimbabwe',
            'prefix' => 263,
            'networks' => [
                [
                    'code' => '21039',
                    'network' => 'Demo Network',
                    'mtPrice' => '0.123'
                ]
            ]
        ]);
        $r['jsonUnserialize'] = [$prefixPrice];

        return $r;
    }

    public function testCannotGetCurrency()
    {
        $this->expectException('\Exception');
        $this->expectExceptionMessage('Currency is unavailable from this endpoint');

        $prefixPrice = new PrefixPrice();
        $prefixPrice->getCurrency();
    }
}
