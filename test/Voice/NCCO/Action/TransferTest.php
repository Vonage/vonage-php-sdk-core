<?php

namespace VonageTest\Voice\NCCO\Action;

use VonageTest\VonageTestCase;
use Vonage\Voice\NCCO\Action\Transfer;

class TransferTest extends VonageTestCase
{
    public function testSimpleSetup(): void
    {
        $this->assertSame([
            'action' => 'transfer',
            'conversation_id' => 'aaaaaaaa-bbbb-cccc-dddd-0123456789ab',
        ], (new Transfer('aaaaaaaa-bbbb-cccc-dddd-0123456789ab'))->jsonSerialize());
    }

    public function testWithOptionals(): void
    {
        $this->assertSame([
            'action' => 'transfer',
            'conversation_id' => 'aaaaaaaa-bbbb-cccc-dddd-0123456789ab',
            'canHear' => ['aaaaaaaa-bbbb-1315-1805-c0ffee'],
            'canSpeak' => ['aaaaaaaa-bbbb-cccc-dddd-c0ffee'],
            'mute' => true,
        ], (Transfer::factory([
            'action' => 'transfer',
            'conversation_id' => 'aaaaaaaa-bbbb-cccc-dddd-0123456789ab',
            'canHear' => ['aaaaaaaa-bbbb-1315-1805-c0ffee'],
            'canSpeak' => ['aaaaaaaa-bbbb-cccc-dddd-c0ffee'],
            'mute' => true,
        ]))->jsonSerialize());
    }
}
