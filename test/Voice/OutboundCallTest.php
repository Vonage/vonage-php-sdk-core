<?php

declare(strict_types=1);

namespace VonageTest\Voice;

use InvalidArgumentException;
use VonageTest\VonageTestCase;
use Vonage\Voice\Endpoint\Phone;
use Vonage\Voice\OutboundCall;

class OutboundCallTest extends VonageTestCase
{
    public function testMachineDetectionThrowsExceptionOnBadValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown machine detection action');

        (new OutboundCall(new Phone('15555555555'), new Phone('16666666666')))
            ->setMachineDetection('bob');
    }
}
