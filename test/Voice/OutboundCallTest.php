<?php

declare(strict_types=1);

namespace VonageTest\Voice;

use Vonage\Voice\VoiceObjects\AdvancedMachineDetection;
use VonageTest\VonageTestCase;
use Vonage\Voice\Endpoint\Phone;
use Vonage\Voice\OutboundCall;

class OutboundCallTest extends VonageTestCase
{
    public function testMachineDetectionThrowsExceptionOnBadValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown machine detection action');

        (new OutboundCall(new Phone('15555555555'), new Phone('16666666666')))
            ->setMachineDetection('bob');
    }

    public function testAdvancedMachineDetectionThrowsExceptionOnBadBehaviour(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('forward is not a valid behavior string');

        $advancedMachineDetection = new AdvancedMachineDetection(
            'forward',
            50,
        );
    }

    public function testAdvancedMachineDetectionThrowsExceptionOnBadMode(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('beep-forward is not a valid mode string');

        $advancedMachineDetection = new AdvancedMachineDetection(
            AdvancedMachineDetection::MACHINE_BEHAVIOUR_CONTINUE,
            50,
            'beep-forward'
        );
    }

    public function testAdvancedMachineDetectionThrowsExceptionOnBadTimeout(): void
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('Timeout 200 is not valid');

        $advancedMachineDetection = new AdvancedMachineDetection(
            AdvancedMachineDetection::MACHINE_BEHAVIOUR_CONTINUE,
            200,
            AdvancedMachineDetection::MACHINE_MODE_DETECT
        );
    }
}
