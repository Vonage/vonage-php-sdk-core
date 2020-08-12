<?php
declare(strict_types=1);

namespace VonageTest\Voice\NCCO\Action;

use Vonage\Voice\NCCO\Action\Talk;
use PHPUnit\Framework\TestCase;

class TalkTest extends TestCase
{
    public function testSimpleSetup()
    {
        $expected = [
            'action' => 'talk',
            'text' => 'Hello',
        ];

        $action = new Talk('Hello');

        $this->assertSame($expected, $action->jsonSerialize());
    }

    public function testJsonSerializeLooksCorrect()
    {
        $expected = [
            'action' => 'talk',
            'text' => 'Hello',
            'bargeIn' => 'false',
            'level' => '0',
            'loop' => '1',
            'voiceName' => 'kimberly'
        ];

        $action = new Talk('Hello');
        $action->setBargeIn(false);
        $action->setLevel(0);
        $action->setLoop(1);
        $action->setVoiceName('kimberly');

        $this->assertSame($expected, $action->jsonSerialize());
    }
}
