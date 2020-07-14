<?php
declare(strict_types=1);

namespace NexmoTest\Voice\NCCO\Action;

use Nexmo\Voice\NCCO\Action\Stream;
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{
    public function testJsonSerializeLooksCorrect()
    {
        $expected = [
            'action' => 'stream',
            'bargeIn' => 'false',
            'level' => '0',
            'loop' => '1',
            'streamUrl' => ['https://test.domain/music.mp3']
        ];

        $action = new Stream('https://test.domain/music.mp3');

        $this->assertSame($expected, $action->jsonSerialize());
    }
}
