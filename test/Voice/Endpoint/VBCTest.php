<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test\Voice\Endpoint;

use PHPUnit\Framework\TestCase;
use Vonage\Voice\Endpoint\VBC;

class VBCTest extends TestCase
{
    public function testSetsExtensionAtCreation(): void
    {
        self::assertSame('123', (new VBC('123'))->getId());
    }

    public function testFactoryCreatesVBCEndpoint(): void
    {
        self::assertSame('123', (VBC::factory('123'))->getId());
    }

    public function testToArrayHasCorrectStructure(): void
    {
        self::assertSame([
            'type' => 'vbc',
            'extension' => '123',
        ], (new VBC('123'))->toArray());
    }

    public function testSerializesToJSONCorrectly(): void
    {
        self::assertSame([
            'type' => 'vbc',
            'extension' => '123',
        ], (new VBC('123'))->jsonSerialize());
    }
}
