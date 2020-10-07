<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test\Insights;

use PHPUnit\Framework\TestCase;
use Vonage\Insights\Advanced;

class AdvancedTest extends TestCase
{
    /**
     * @dataProvider advancedTestProvider
     * @param $advanced
     * @param $inputData
     */
    public function testArrayAccess($advanced, $inputData): void
    {
        self::assertEquals($inputData['valid_number'], @$advanced['valid_number']);
        self::assertEquals($inputData['reachable'], @$advanced['reachable']);
    }

    /**
     * @dataProvider advancedTestProvider
     * @param $advanced
     * @param $inputData
     */
    public function testObjectAccess($advanced, $inputData): void
    {
        self::assertEquals($inputData['valid_number'], $advanced->getValidNumber());
        self::assertEquals($inputData['reachable'], $advanced->getReachable());
    }

    /**
     * @return array
     */
    public function advancedTestProvider(): array
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
