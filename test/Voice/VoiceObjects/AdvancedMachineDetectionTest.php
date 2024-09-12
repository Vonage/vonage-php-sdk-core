<?php

namespace VonageTest\Voice\VoiceObjects;

use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use Vonage\Voice\VoiceObjects\AdvancedMachineDetection;

class AdvancedMachineDetectionTest extends TestCase
{
    public function testValidConstructor()
    {
        $amd = new AdvancedMachineDetection('continue', 60, 'detect');
        $this->assertInstanceOf(AdvancedMachineDetection::class, $amd);
    }

    public function testInvalidBehaviour()
    {
        $this->expectException(InvalidArgumentException::class);
        new AdvancedMachineDetection('invalid_behaviour', 60, 'detect');
    }

    public function testInvalidMode()
    {
        $this->expectException(InvalidArgumentException::class);
        new AdvancedMachineDetection('continue', 60, 'invalid_mode');
    }

    public function testInvalidBeepTimeout()
    {
        $this->expectException(OutOfBoundsException::class);
        new AdvancedMachineDetection('continue', 150, 'detect');
    }

    public function testValidBeepTimeoutRange()
    {
        $amd = new AdvancedMachineDetection('hangup', 100, 'detect_beep');
        $this->assertEquals(100, $amd->toArray()['beep_timeout']);
    }

    public function testWillRenderDefault()
    {
        $amd = new AdvancedMachineDetection('hangup', 100, 'default');
        $this->assertEquals('default', $amd->toArray()['mode']);
    }

    public function testToArray()
    {
        $amd = new AdvancedMachineDetection('continue', 45, 'detect');
        $expected = [
            'behavior' => 'continue',
            'mode' => 'detect',
            'beep_timeout' => 45
        ];

        $this->assertEquals($expected, $amd->toArray());
    }

    public function testFromArrayValid()
    {
        $data = [
            'behaviour' => 'hangup',
            'mode' => 'detect_beep',
            'beep_timeout' => 60
        ];

        $amd = (new AdvancedMachineDetection('continue', 45))->fromArray($data);
        $this->assertEquals('hangup', $amd->toArray()['behavior']);
        $this->assertEquals('detect_beep', $amd->toArray()['mode']);
        $this->assertEquals(60, $amd->toArray()['beep_timeout']);
    }

    public function testFromArrayInvalidData()
    {
        $this->expectException(InvalidArgumentException::class);

        $data = [
            'behaviour' => 'invalid_behaviour',
            'mode' => 'detect',
            'beep_timeout' => 60
        ];

        (new AdvancedMachineDetection('continue', 45))->fromArray($data);
    }
}
