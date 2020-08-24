<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Insights;

use Vonage\Insights\Advanced;
use PHPUnit\Framework\TestCase;

class AdvancedTest extends TestCase
{

    /**
     * @dataProvider advancedTestProvider
     */
    public function testArrayAccess($advanced, $inputData)
    {
        $this->assertEquals($inputData['valid_number'], @$advanced['valid_number']);
        $this->assertEquals($inputData['reachable'], @$advanced['reachable']);
    }

    /**
     * @dataProvider advancedTestProvider
     */
    public function testObjectAccess($advanced, $inputData)
    {
        $this->assertEquals($inputData['valid_number'], $advanced->getValidNumber());
        $this->assertEquals($inputData['reachable'], $advanced->getReachable());
    }

    public function advancedTestProvider()
    {
        $r = [];

        $input1 = [
            'valid_number' => 'valid',
            'reachable' => 'unknown'
        ];

        $advanced1 = new Advanced('01234567890');
        $advanced1->fromArray($input1);
        $r['standard-1'] = [$advanced1, $input1];

        return $r;
    }
}
