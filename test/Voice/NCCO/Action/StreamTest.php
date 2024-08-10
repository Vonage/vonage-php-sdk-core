<?php

declare(strict_types=1);

namespace VonageTest\Voice\NCCO\Action;

use VonageTest\VonageTestCase;
use Vonage\Voice\NCCO\Action\Stream;

class StreamTest extends VonageTestCase
{
    public function testSimpleSetup(): void
    {
        $this->assertSame([
            'action' => 'stream',
            'streamUrl' => ['https://test.domain/music.mp3']
        ], (new Stream('https://test.domain/music.mp3'))->toNCCOArray());
    }

    public function testJsonSerializeLooksCorrect(): void
    {
        $this->assertSame([
            'action' => 'stream',
            'streamUrl' => ['https://test.domain/music.mp3'],
            'bargeIn' => 'false',
            'level' => '0',
            'loop' => '1',
        ], (new Stream('https://test.domain/music.mp3'))
            ->setBargeIn(false)
            ->setLevel(0)
            ->setLoop(1)
            ->jsonSerialize());
    }

    public function testFactoryWithArray(): void
    {
        $this->assertSame([
            'action' => 'stream',
            'streamUrl' => ['https://test.domain/music.mp3']
        ], Stream::factory(['https://test.domain/music.mp3'], [])->toNCCOArray());
    }

    public function testFactoryWithString(): void
    {
        $this->assertSame([
            'action' => 'stream',
            'streamUrl' => ['https://test.domain/music.mp3']
        ], Stream::factory('https://test.domain/music.mp3', [])->toNCCOArray());
    }
}
