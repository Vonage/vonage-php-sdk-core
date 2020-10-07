<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test\Voice\NCCO\Action;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Vonage\Voice\NCCO\Action\Record;

class RecordTest extends TestCase
{
    public function testWebhookMethodCanBeSetInFactory(): void
    {
        $action = Record::factory([
            'eventUrl' => 'https://test.domain/recording',
            'eventMethod' => 'GET'
        ]);

        self::assertSame('GET', $action->getEventWebhook()->getMethod());
        self::assertSame('GET', $action->toNCCOArray()['eventMethod']);
    }

    public function testJsonSerializeLooksCorrect(): void
    {
        self::assertSame([
            'action' => 'record',
            'format' => 'mp3',
            'timeOut' => '7200',
            'beepStart' => 'false'
        ], (new Record())->jsonSerialize());
    }

    public function testSettingChannelBackToOneResetsValues(): void
    {
        $action = new Record();

        self::assertNull($action->getSplit());
        self::assertNull($action->getChannels());

        $action->setChannels(2);

        self::assertSame(Record::SPLIT, $action->getSplit());
        self::assertSame(2, $action->getChannels());

        $action->setChannels(1);

        self::assertNull($action->getSplit());
        self::assertNull($action->getChannels());
    }

    public function testCannotSetTooManyChannels(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Number of channels must be 32 or less');

        (new Record())->setChannels(100);
    }

    public function testCannotSetInvalidTimeout(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('TimeOut value must be between 3 and 7200 seconds, inclusive');

        (new Record())->setTimeout(1);
    }

    public function testCannotSetInvalidSilenceTimeout(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('End On Silence value must be between 3 and 10 seconds, inclusive');

        (new Record())->setEndOnSilence(1);
    }

    public function testCannotSetInvalidEndOnKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid End on Key character');

        (new Record())->setEndOnKey('h');
    }

    public function testCannotSetInvalidSplitValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Split value must be "conversation" if enabling');

        (new Record())->setSplit('foo');
    }
}
