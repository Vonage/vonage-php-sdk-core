<?php
declare(strict_types=1);

namespace VonageTest\SMS\Message;

use Vonage\SMS\Message\WAPPush;
use PHPUnit\Framework\TestCase;

class WAPPushTest extends TestCase
{
    public function testCanCreateWAPMessage()
    {
        $message = new WAPPush(
            '447700900000',
            '16105551212',
            'Check In Now!',
            'https://test.domain/check-in',
            300000
        );

        $data = $message->toArray();

        $this->assertSame('447700900000', $data['to']);
        $this->assertSame('16105551212', $data['from']);
        $this->assertSame('Check In Now!', $data['title']);
        $this->assertSame('https://test.domain/check-in', $data['url']);
        $this->assertSame(300000, $data['validity']);
    }
}
