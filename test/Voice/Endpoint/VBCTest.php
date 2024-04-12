<?php

declare(strict_types=1);

namespace VonageTest\Voice\Endpoint;

use VonageTest\VonageTestCase;
use Vonage\Voice\Endpoint\VBC;

class VBCTest extends VonageTestCase
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
