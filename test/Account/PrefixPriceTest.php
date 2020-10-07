<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test\Account;

use Exception;
use PHPUnit\Framework\TestCase;
use Vonage\Account\PrefixPrice;

class PrefixPriceTest extends TestCase
{
    /**
     * @dataProvider prefixPriceProvider
     * @param $prefixPrice
     */
    public function testFromArray($prefixPrice): void
    {
        self::assertEquals("ZW", $prefixPrice->getCountryCode());
        self::assertEquals("Zimbabwe", $prefixPrice->getCountryName());
        self::assertEquals("263", $prefixPrice->getDialingPrefix());
    }

    /**
     * @dataProvider prefixPriceProvider
     * @param $prefixPrice
     */
    public function testGetters($prefixPrice): void
    {
        self::assertEquals("ZW", $prefixPrice->getCountryCode());
        self::assertEquals("Zimbabwe", $prefixPrice->getCountryName());
        self::assertEquals("Zimbabwe", $prefixPrice->getCountryDisplayName());
        self::assertEquals("263", $prefixPrice->getDialingPrefix());
    }

    /**
     * @dataProvider prefixPriceProvider
     * @param $prefixPrice
     */
    public function testArrayAccess($prefixPrice): void
    {
        self::assertEquals("ZW", @$prefixPrice['country_code']);
        self::assertEquals("Zimbabwe", @$prefixPrice['country_name']);
        self::assertEquals("Zimbabwe", @$prefixPrice['country_display_name']);
        self::assertEquals("263", @$prefixPrice['dialing_prefix']);
    }

    /**
     * @dataProvider prefixPriceProvider
     * @param $prefixPrice
     */
    public function testUsesCustomPriceForKnownNetwork($prefixPrice): void
    {
        self::assertEquals("0.123", $prefixPrice->getPriceForNetwork('21039'));
    }

    /**
     * @return array
     */
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
