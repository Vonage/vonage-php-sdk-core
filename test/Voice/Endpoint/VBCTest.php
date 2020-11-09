<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Voice\Endpoint;

use PHPUnit\Framework\TestCase;
use Vonage\Voice\Endpoint\VBC;

class VBCTest extends TestCase
{
    public function testSetsExtensionAtCreation(): void
    {
        $this->assertSame('123', (new VBC('123'))->getId());
    }

    public function testFactoryCreatesVBCEndpoint(): void
    {
        $this->assertSame('123', (VBC::factory('123'))->getId());
    }

    public function testToArrayHasCorrectStructure(): void
    {
        $this->assertSame([
            'type' => 'vbc',
            'extension' => '123',
        ], (new VBC('123'))->toArray());
    }

    public function testSerializesToJSONCorrectly(): void
    {
        $this->assertSame([
            'type' => 'vbc',
            'extension' => '123',
        ], (new VBC('123'))->jsonSerialize());
    }
}
