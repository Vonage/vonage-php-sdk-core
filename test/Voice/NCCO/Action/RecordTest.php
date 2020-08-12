<?php
declare(strict_types=1);

namespace VonageTest\Voice\NCCO\Action;

use Vonage\Voice\NCCO\Action\Record;
use PHPUnit\Framework\TestCase;

class RecordTest extends TestCase
{
    public function testWebhookMethodCanBeSetInFactory()
    {
        $action = Record::factory([
            'eventUrl' => 'https://test.domain/recording',
            'eventMethod' => 'GET'
        ]);
        $this->assertSame('GET', $action->getEventWebhook()->getMethod());

        $ncco = $action->toNCCOArray();
        $this->assertSame('GET', $ncco['eventMethod']);
    }

    public function testJsonSerializeLooksCorrect()
    {
        $expected = [
            'action' => 'record',
            'format' => 'mp3',
            'timeOut' => '7200',
            'beepStart' => 'false'
        ];

        $action = new Record();

        $this->assertSame($expected, $action->jsonSerialize());
    }

    public function testSettingChannelBackToOneResetsValues()
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

    public function testCannotSetTooManyChannels()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Number of channels must be 32 or less');

        $action = new Record();
        $action->setChannels(100);
    }

    public function testCannotSetInvalidTimeout()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('TimeOut value must be between 3 and 7200 seconds, inclusive');

        $action = new Record();
        $action->setTimeout(1);
    }

    public function testCannotSetInvalidSilenceTimeout()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('End On Silence value must be between 3 and 10 seconds, inclusive');

        $action = new Record();
        $action->setEndOnSilence(1);
    }

    public function testCannotSetInvalidEndOnKey()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid End on Key character');

        $action = new Record();
        $action->setEndOnKey('h');
    }

    public function testCannotSetInvalidSplitValue()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Split value must be "conversation" if enabling');

        $action = new Record();
        $action->setSplit('foo');
    }
}
