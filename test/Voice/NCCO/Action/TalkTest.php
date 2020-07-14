<?php
declare(strict_types=1);

namespace NexmoTest\Voice\NCCO\Action;

use Nexmo\Voice\NCCO\Action\Talk;
use PHPUnit\Framework\TestCase;

class TalkTest extends TestCase
{
    public function testJsonSerializeLooksCorrect()
    {
        $expected = [
            'action' => 'talk',
            'bargeIn' => 'false',
            'level' => '0',
            'loop' => '1',
            'text' => 'Hello',
            'voiceName' => 'kimberly'
        ];

        $action = new Talk('Hello');

        $this->assertSame($expected, $action->jsonSerialize());
    }
}
