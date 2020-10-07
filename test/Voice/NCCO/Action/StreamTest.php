<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test\Voice\NCCO\Action;

use PHPUnit\Framework\TestCase;
use Vonage\Voice\NCCO\Action\Stream;

class StreamTest extends TestCase
{
    public function testSimpleSetup(): void
    {
        self::assertSame([
            'action' => 'stream',
            'streamUrl' => ['https://test.domain/music.mp3']
        ], (new Stream('https://test.domain/music.mp3'))->toNCCOArray());
    }

    public function testJsonSerializeLooksCorrect(): void
    {
        self::assertSame([
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
}
