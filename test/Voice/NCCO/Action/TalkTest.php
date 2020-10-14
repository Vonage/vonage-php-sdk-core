<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\Test\Voice\NCCO\Action;

use PHPUnit\Framework\TestCase;
use Vonage\Voice\NCCO\Action\Talk;

class TalkTest extends TestCase
{
    public function testSimpleSetup(): void
    {
        self::assertSame([
            'action' => 'talk',
            'text' => 'Hello',
        ], (new Talk('Hello'))->jsonSerialize());
    }

    public function testJsonSerializeLooksCorrect(): void
    {
        self::assertSame([
            'action' => 'talk',
            'text' => 'Hello',
            'bargeIn' => 'false',
            'level' => '0',
            'loop' => '1',
            'voiceName' => 'kimberly'
        ], (new Talk('Hello'))
            ->setBargeIn(false)
            ->setLevel(0)
            ->setLoop(1)
            ->setVoiceName('kimberly')
            ->jsonSerialize());
    }
}
