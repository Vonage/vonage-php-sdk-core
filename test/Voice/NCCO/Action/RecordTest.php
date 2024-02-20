<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Voice\NCCO\Action;

use InvalidArgumentException;
use VonageTest\VonageTestCase;
use Vonage\Voice\NCCO\Action\Record;

class RecordTest extends VonageTestCase
{
    public function testWebhookMethodCanBeSetInFactory(): void
    {
        $action = Record::factory([
            'eventUrl' => 'https://test.domain/recording',
            'eventMethod' => 'GET'
        ]);

        $this->assertSame('GET', $action->getEventWebhook()->getMethod());
        $this->assertSame('GET', $action->toNCCOArray()['eventMethod']);
    }

    public function testJsonSerializeLooksCorrect(): void
    {
        $record = new Record();
        $this->assertSame([
            'action' => 'record',
            'format' => 'mp3',
            'beepStart' => 'false'
        ], $record->jsonSerialize());

        $record->setTimeout(1234);
        $this->assertSame([
            'action' => 'record',
            'format' => 'mp3',
            'beepStart' => 'false',
            'timeOut' => '1234'
        ], $record->jsonSerialize());
    }

    public function testSettingChannelBackToOneResetsValues(): void
    {
        $action = new Record();

        $this->assertNull($action->getSplit());
        $this->assertNull($action->getChannels());

        $action->setChannels(2);

        $this->assertSame(Record::SPLIT, $action->getSplit());
        $this->assertSame(2, $action->getChannels());

        $action->setChannels(1);

        $this->assertNull($action->getSplit());
        $this->assertNull($action->getChannels());
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
