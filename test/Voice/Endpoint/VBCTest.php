<?php
declare(strict_types=1);

namespace VonageTest\Voice\Endpoint;

use Vonage\Voice\Endpoint\VBC;
use PHPUnit\Framework\TestCase;

class VBCTest extends TestCase
{
    public function testSetsExtensionAtCreation()
    {
        $endpoint = new VBC("123");
        $this->assertSame("123", $endpoint->getId());
    }

    public function testFactoryCreatesVBCEndpoint()
    {
        $endpoint = VBC::factory('123');
        $this->assertSame("123", $endpoint->getId());
    }

    public function testToArrayHasCorrectStructure()
    {
        $expected = [
            'type' => 'vbc',
            'extension' => '123',
        ];
        
        $endpoint = new VBC("123");
        $this->assertSame($expected, $endpoint->toArray());
    }

    public function testSerializesToJSONCorrectly()
    {
        $expected = [
            'type' => 'vbc',
            'extension' => '123',
        ];
        
        $endpoint = new VBC("123");
        $this->assertSame($expected, $endpoint->jsonSerialize());
    }
}
