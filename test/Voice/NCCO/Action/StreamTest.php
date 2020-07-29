<?php
declare(strict_types=1);

namespace NexmoTest\Voice\NCCO\Action;

use Nexmo\Voice\NCCO\Action\ActionInterface;
use Nexmo\Voice\NCCO\Action\Stream;
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{
    public function testSimpleSetup()
    {
        $expected = [
            'action' => 'stream',
            'streamUrl' => ['https://test.domain/music.mp3']
        ];

        $action = new Stream('https://test.domain/music.mp3');

        $this->assertSame($expected, $action->toNCCOArray());
    }

    public function testJsonSerializeLooksCorrect()
    {
        $expected = [
            'action' => 'stream',
            'streamUrl' => ['https://test.domain/music.mp3'],
            'bargeIn' => 'false',
            'level' => '0',
            'loop' => '1',
        ];

        $action = new Stream('https://test.domain/music.mp3');
        $action->setBargeIn(false);
        $action->setLevel(0);
        $action->setLoop(1);

        $this->assertSame($expected, $action->jsonSerialize());
    }
}
