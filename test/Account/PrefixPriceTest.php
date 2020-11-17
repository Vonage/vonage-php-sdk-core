<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Account;

use Exception;
use PHPUnit\Framework\TestCase;
use Vonage\Account\PrefixPrice;

class PrefixPriceTest extends TestCase
{
    /**
     * @dataProvider prefixPriceProvider
     *
     * @param $prefixPrice
     */
    public function testFromArray($prefixPrice): void
    {
        $this->assertEquals("ZW", $prefixPrice->getCountryCode());
        $this->assertEquals("Zimbabwe", $prefixPrice->getCountryName());
        $this->assertEquals("263", $prefixPrice->getDialingPrefix());
    }

    /**
     * @dataProvider prefixPriceProvider
     *
     * @param $prefixPrice
     */
    public function testGetters($prefixPrice): void
    {
        $this->assertEquals("ZW", $prefixPrice->getCountryCode());
        $this->assertEquals("Zimbabwe", $prefixPrice->getCountryName());
        $this->assertEquals("Zimbabwe", $prefixPrice->getCountryDisplayName());
        $this->assertEquals("263", $prefixPrice->getDialingPrefix());
    }

    /**
     * @dataProvider prefixPriceProvider
     *
     * @param $prefixPrice
     */
    public function testArrayAccess($prefixPrice): void
    {
        $this->assertEquals("ZW", @$prefixPrice['country_code']);
        $this->assertEquals("Zimbabwe", @$prefixPrice['country_name']);
        $this->assertEquals("Zimbabwe", @$prefixPrice['country_display_name']);
        $this->assertEquals("263", @$prefixPrice['dialing_prefix']);
    }

    /**
     * @dataProvider prefixPriceProvider
     *
     * @param $prefixPrice
     */
    public function testUsesCustomPriceForKnownNetwork($prefixPrice): void
    {
        $this->assertEquals("0.123", $prefixPrice->getPriceForNetwork('21039'));
    }

    public function prefixPriceProvider(): array
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

    /**
     * @throws \Vonage\Client\Exception\Exception
     */
    public function testCannotGetCurrency(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Currency is unavailable from this endpoint');

        $prefixPrice = new PrefixPrice();
        $prefixPrice->getCurrency();
    }
}
