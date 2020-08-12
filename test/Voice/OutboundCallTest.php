<?php
declare(strict_types=1);

namespace VonageTest\Voice;

use Vonage\Voice\OutboundCall;
use Vonage\Voice\Endpoint\Phone;
use PHPUnit\Framework\TestCase;

class OutboundCallTest extends TestCase
{
    public function testMachineDetectionThrowsExceptionOnBadValue()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown machine detection action');

        (new OutboundCall(new Phone('15555555555'), new Phone('16666666666')))
            ->setMachineDetection('bob')
        ;
    }
}
