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
use Vonage\Insights\Standard;

class StandardTest extends TestCase
{
    /**
     * @dataProvider standardTestProvider
     * @param $standard
     * @param $inputData
     */
    public function testArrayAccess($standard, $inputData): void
    {
        self::assertEquals($inputData['refund_price'], @$standard['refund_price']);
        self::assertEquals($inputData['request_price'], @$standard['request_price']);
        self::assertEquals($inputData['remaining_balance'], @$standard['remaining_balance']);
        self::assertEquals($inputData['current_carrier'], @$standard['current_carrier']);
        self::assertEquals($inputData['original_carrier'], @$standard['original_carrier']);
        self::assertEquals($inputData['ported'], @$standard['ported']);
        self::assertEquals($inputData['roaming'], @$standard['roaming']);
    }

    /**
     * @dataProvider standardTestProvider
     * @param $standard
     * @param $inputData
     */
    public function testObjectAccess($standard, $inputData): void
    {
        self::assertEquals($inputData['refund_price'], @$standard->getRefundPrice());
        self::assertEquals($inputData['request_price'], @$standard->getRequestPrice());
        self::assertEquals($inputData['remaining_balance'], @$standard->getRemainingBalance());
        self::assertEquals($inputData['current_carrier'], $standard->getCurrentCarrier());
        self::assertEquals($inputData['original_carrier'], $standard->getOriginalCarrier());
        self::assertEquals($inputData['ported'], $standard->getPorted());
        self::assertEquals($inputData['roaming'], $standard->getRoaming());
    }

    /**
     * @return array
     */
    public function standardTestProvider(): array
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
