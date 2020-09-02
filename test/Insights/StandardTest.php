<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Insights;

use Vonage\Insights\Basic;
use Vonage\Insights\Standard;
use PHPUnit\Framework\TestCase;

class StandardTest extends TestCase
{

    /**
     * @dataProvider standardTestProvider
     */
    public function testArrayAccess($standard, $inputData)
    {
        $this->assertEquals($inputData['refund_price'], @$standard['refund_price']);
        $this->assertEquals($inputData['request_price'], @$standard['request_price']);
        $this->assertEquals($inputData['remaining_balance'], @$standard['remaining_balance']);
        $this->assertEquals($inputData['current_carrier'], @$standard['current_carrier']);
        $this->assertEquals($inputData['original_carrier'], @$standard['original_carrier']);
        $this->assertEquals($inputData['ported'], @$standard['ported']);
        $this->assertEquals($inputData['roaming'], @$standard['roaming']);
    }

    /**
     * @dataProvider standardTestProvider
     */
    public function testObjectAccess($standard, $inputData)
    {
        $this->assertEquals($inputData['refund_price'], @$standard->getRefundPrice());
        $this->assertEquals($inputData['request_price'], @$standard->getRequestPrice());
        $this->assertEquals($inputData['remaining_balance'], @$standard->getRemainingBalance());
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
            'request_price' => '0.00500000',
            'refund_price' => '0.00500000',
            'remaining_balance' => '26.294675',
            'roaming' =>
                [
                    'status' => 'unknown',
                ],
        ];

        $standard1 = new Standard('01234567890');
        $standard1->fromArray($input1);
        $r['standard-1'] = [$standard1, $input1];

        return $r;
    }
}
