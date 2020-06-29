<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Insights;

use Nexmo\Insights\Basic;
use PHPUnit\Framework\TestCase;

class BasicTest extends TestCase
{
    /**
     * @dataProvider basicTestProvider
     */
    public function testObjectAccess($basic, $inputData)
    {
        $this->assertEquals($inputData['request_id'], $basic->getRequestId());
        $this->assertEquals($inputData['international_format_number'], $basic->getInternationalFormatNumber());
        $this->assertEquals($inputData['national_format_number'], $basic->getNationalFormatNumber());
        $this->assertEquals($inputData['country_code'], $basic->getCountryCode());
        $this->assertEquals($inputData['country_code_iso3'], $basic->getCountryCodeISO3());
        $this->assertEquals($inputData['country_name'], $basic->getCountryName());
        $this->assertEquals($inputData['country_prefix'], $basic->getCountryPrefix());
    }

    public function basicTestProvider()
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
                'country_prefix' => '44',
        ];

        $basic1 = new Basic($inputBasic1['national_format_number']);
        $basic1->fromArray($inputBasic1);
        $r['basic-1'] = [$basic1, $inputBasic1];

        return $r;
    }
}
