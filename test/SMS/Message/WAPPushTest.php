<?php

declare(strict_types=1);

namespace VonageTest\SMS\Message;

use VonageTest\VonageTestCase;
use Vonage\SMS\Message\WAPPush;

class WAPPushTest extends VonageTestCase
{
    public function testCanCreateWAPMessage(): void
    {
        $data = (new WAPPush(
            '447700900000',
            '16105551212',
            'Check In Now!',
            'https://test.domain/check-in',
            300000
        ))->toArray();

        $this->assertSame('447700900000', $data['to']);
        $this->assertSame('16105551212', $data['from']);
        $this->assertSame('Check In Now!', $data['title']);
        $this->assertSame('https://test.domain/check-in', $data['url']);
        $this->assertSame(300000, $data['validity']);
    }
}
