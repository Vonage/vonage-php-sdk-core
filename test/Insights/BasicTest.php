<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Insights;

use PHPUnit\Framework\TestCase;
use Vonage\Insights\Basic;

class BasicTest extends TestCase
{
    /**
     * @dataProvider basicTestProvider
     *
     * @param $basic
     * @param $inputData
     */
    public function testArrayAccess($basic, $inputData): void
    {
        $this->assertEquals($inputData['request_id'], @$basic['request_id']);
        $this->assertEquals($inputData['international_format_number'], @$basic['international_format_number']);
        $this->assertEquals($inputData['national_format_number'], @$basic['national_format_number']);
        $this->assertEquals($inputData['country_code'], @$basic['country_code']);
        $this->assertEquals($inputData['country_code_iso3'], @$basic['country_code_iso3']);
        $this->assertEquals($inputData['country_name'], @$basic['country_name']);
        $this->assertEquals($inputData['country_prefix'], @$basic['country_prefix']);
    }

    /**
     * @dataProvider basicTestProvider
     *
     * @param $basic
     * @param $inputData
     */
    public function testObjectAccess($basic, $inputData): void
    {
        $this->assertEquals($inputData['request_id'], $basic->getRequestId());
        $this->assertEquals($inputData['international_format_number'], $basic->getInternationalFormatNumber());
        $this->assertEquals($inputData['national_format_number'], $basic->getNationalFormatNumber());
        $this->assertEquals($inputData['country_code'], $basic->getCountryCode());
        $this->assertEquals($inputData['country_code_iso3'], $basic->getCountryCodeISO3());
        $this->assertEquals($inputData['country_name'], $basic->getCountryName());
        $this->assertEquals($inputData['country_prefix'], $basic->getCountryPrefix());
    }

    public function basicTestProvider(): array
    {
        $r = [];

        $inputBasic1 = [
                'status' => 0,
                'status_message' => 'Success',
                'request_id' => 'cc903ddb-4427-421b-8938-8b377cd76710',
                'international_format_number' => '447908123456',
                'national_format_number' => '07908 123456',
                'country_code' => 'GB',
                'country_code_iso3' => 'GBR',
                'country_name' => 'United Kingdom',
                'country_prefix' => 44,
        ];

        $basic1 = new Basic($inputBasic1['national_format_number']);
        $basic1->fromArray($inputBasic1);
        $r['basic-1'] = [$basic1, $inputBasic1];

        return $r;
    }
}
