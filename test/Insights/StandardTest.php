<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Insights;

use Nexmo\Insights\Basic;
use Nexmo\Insights\Standard;

class StandardTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider standardTestProvider
     */
    public function testArrayAccess($standard, $inputData)
    {
        $this->assertEquals($inputData['current_carrier'], $standard['current_carrier']);
        $this->assertEquals($inputData['original_carrier'], $standard['original_carrier']);
        $this->assertEquals($inputData['ported'], $standard['ported']);
        $this->assertEquals($inputData['roaming'], $standard['roaming']);
    }

    /**
     * @dataProvider standardTestProvider
     */
    public function testObjectAccess($standard, $inputData)
    {
        $this->assertEquals($inputData['current_carrier'], $standard->getCurrentCarrier());
        $this->assertEquals($inputData['original_carrier'], $standard->getOriginalCarrier());
        $this->assertEquals($inputData['ported'], $standard->getPorted());
        $this->assertEquals($inputData['roaming'], $standard->getRoaming());
    }

    public function standardTestProvider()
    {
        $r = [];

        $input1 = [
            'current_carrier' =>
                [
                    'network_code' => '23420',
                    'name' => 'Hutchison 3G Ltd',
                    'country' => 'GB',
                    'network_type' => 'mobile',
                ],
            'original_carrier' =>
                [
                    'network_code' => '23430',
                    'name' => 'EE Tmobile',
                    'country' => 'GB',
                    'network_type' => 'mobile',
                ],
            'ported' => 'assumed_ported',
            'roaming' =>
                [
                    'status' => 'unknown',
                ],
        ];

        $standard1 = new Standard('01234567890');
        $standard1->jsonUnserialize($input1);
        $r['standard-1'] = [$standard1, $input1];

        return $r;
    }
}
