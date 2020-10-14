<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\Test\Insights;

use PHPUnit\Framework\TestCase;
use Vonage\Insights\CnamTrait;
use Vonage\Insights\Standard;

class CnamTraitTest extends TestCase
{

    /**
     * @dataProvider cnamProvider
     * @param $cnam
     * @param $inputData
     */
    public function testArrayAccess($cnam, $inputData): void
    {
        self::assertEquals($inputData['first_name'], @$cnam['first_name']);
        self::assertEquals($inputData['last_name'], @$cnam['last_name']);
        self::assertEquals($inputData['caller_name'], @$cnam['caller_name']);
        self::assertEquals($inputData['caller_type'], @$cnam['caller_type']);
    }

    /**
     * @dataProvider cnamProvider
     * @param $cnam
     * @param $inputData
     */
    public function testObjectAccess($cnam, $inputData): void
    {
        self::assertEquals($inputData['first_name'], $cnam->getFirstName());
        self::assertEquals($inputData['last_name'], $cnam->getLastName());
        self::assertEquals($inputData['caller_name'], $cnam->getCallerName());
        self::assertEquals($inputData['caller_type'], $cnam->getCallerType());
    }

    /**
     * @return array
     */
    public function cnamProvider(): array
    {
        $r = [];

        $input1 = [
            'first_name' => 'Tony',
            'last_name' => 'Tiger',
            'caller_name' => 'Tony Tiger Esq',
            'caller_type' => 'consumer'
        ];

        $cnam1 = new Cnam('14155550100');
        $cnam1->fromArray($input1);
        $r['cnam-1'] = [$cnam1, $input1];

        return $r;
    }
}

class Cnam extends Standard
{
    use CnamTrait;
}
