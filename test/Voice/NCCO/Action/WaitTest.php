<?php

namespace VonageTest\Voice\NCCO\Action;

use VonageTest\VonageTestCase;
use Vonage\Voice\NCCO\Action\Wait;

class WaitTest extends VonageTestCase
{
    public function testSimpleSetup(): void
    {
        $this->assertSame([
            'action' => 'wait',
        ], (new Wait())->jsonSerialize());
    }

    public function testWithTimeout(): void
    {
        $this->assertSame([
            'action' => 'wait',
            'timeout' => 0.5
        ], (new Wait(0.5))->jsonSerialize());
    }
}
