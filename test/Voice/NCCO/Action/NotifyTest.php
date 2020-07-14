<?php
declare(strict_types=1);

namespace NexmoTest\Voice\NCCO\Action;

use Nexmo\Voice\Webhook;
use PHPUnit\Framework\TestCase;
use Nexmo\Voice\NCCO\Action\Notify;

class NotifyTest extends TestCase
{
    public function testCanSetAdditionalInformation()
    {
        $webhook = new Webhook('https://test.domain/events');

        $action = new Notify(['foo' => 'bar']);
        $action->setEventWebhook($webhook);

        $this->assertSame(['foo' => 'bar'], $action->getPayload());
        $this->assertSame($webhook, $action->getEventWebhook());
    }

    public function testCanGenerateFromFactory()
    {
        $data = [
            'action' => 'notify',
            'payload' => ['foo' => 'bar'],
            'eventUrl' => 'https://test.domain/events',
        ];

        $action = Notify::factory(['foo' => 'bar'], $data);

        $this->assertSame(['foo' => 'bar'], $action->getPayload());
        $this->assertSame('https://test.domain/events', $action->getEventWebhook()->getUrl());
        $this->assertSame('POST', $action->getEventWebhook()->getMethod());
    }

    public function testGeneratesCorrectNCCOArray()
    {
        $webhook = new Webhook('https://test.domain/events');

        $action = new Notify(['foo' => 'bar']);
        $action->setEventWebhook($webhook);

        $ncco = $action->toNCCOArray();

        $this->assertSame('notify', $ncco['action']);
        $this->assertSame(['foo' => 'bar'], $ncco['payload']);
        $this->assertSame('https://test.domain/events', $ncco['eventUrl']);
        $this->assertSame('POST', $ncco['eventMethod']);
    }

    public function testJSONSerializesToCorrectStructure()
    {
        $webhook = new Webhook('https://test.domain/events');

        $action = new Notify(['foo' => 'bar']);
        $action->setEventWebhook($webhook);

        $ncco = $action->jsonSerialize();

        $this->assertSame('notify', $ncco['action']);
        $this->assertSame(['foo' => 'bar'], $ncco['payload']);
        $this->assertSame('https://test.domain/events', $ncco['eventUrl']);
        $this->assertSame('POST', $ncco['eventMethod']);
    }

    public function testCanAddToPayload()
    {
        $action = new Notify(['foo' => 'bar']);
        $action->addToPayload('baz', 'biff');

        $this->assertSame(['foo' => 'bar', 'baz' => 'biff'], $action->getPayload());
    }
}
