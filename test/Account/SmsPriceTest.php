<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test\Account;

use PHPUnit\Framework\TestCase;
use Vonage\Account\SmsPrice;

class SmsPriceTest extends TestCase
{
    /**
     * @dataProvider smsPriceProvider
     * @param $smsPrice
     */
    public function testFromArray($smsPrice): void
    {
        self::assertEquals("US", $smsPrice->getCountryCode());
        self::assertEquals("United States", $smsPrice->getCountryName());
        self::assertEquals("1", $smsPrice->getDialingPrefix());
        self::assertEquals("0.00512", $smsPrice->getDefaultPrice());
    }

    /**
     * @dataProvider smsPriceProvider
     * @param $smsPrice
     */
    public function testGetters($smsPrice): void
    {
        self::assertEquals("US", $smsPrice->getCountryCode());
        self::assertEquals("United States", $smsPrice->getCountryName());
        self::assertEquals("United States", $smsPrice->getCountryDisplayName());
        self::assertEquals("1", $smsPrice->getDialingPrefix());
        self::assertEquals("0.00512", $smsPrice->getDefaultPrice());
    }

    /**
     * @dataProvider smsPriceProvider
     * @param $smsPrice
     */
    public function testArrayAccess($smsPrice): void
    {
        self::assertEquals("US", @$smsPrice['country_code']);
        self::assertEquals("United States", @$smsPrice['country_name']);
        self::assertEquals("United States", @$smsPrice['country_display_name']);
        self::assertEquals("1", @$smsPrice['dialing_prefix']);
        self::assertEquals("0.00512", @$smsPrice['default_price']);
    }

    /**
     * @dataProvider smsPriceProvider
     * @param $smsPrice
     */
    public function testUsesCustomPriceForKnownNetwork($smsPrice): void
    {
        self::assertEquals("0.123", $smsPrice->getPriceForNetwork('21039'));
    }

    /**
     * @dataProvider smsPriceProvider
     * @param $smsPrice
     */
    public function testUsesDefaultPriceForUnknownNetwork($smsPrice): void
    {
        self::assertEquals("0.00512", $smsPrice->getPriceForNetwork('007'));
    }

    public function smsPriceProvider(): array
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
