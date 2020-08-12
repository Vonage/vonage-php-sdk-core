<?php
declare(strict_types=1);

namespace VonageTest\Voice\NCCO\Action;

use Vonage\Voice\Webhook;
use PHPUnit\Framework\TestCase;
use Vonage\Voice\NCCO\Action\Notify;

class NotifyTest extends TestCase
{
    public function testCanSetAdditionalInformation()
    {
        $webhook = new Webhook('https://test.domain/events');

        $action = new Notify(['foo' => 'bar'], $webhook);
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

        $action = new Notify(['foo' => 'bar'], $webhook);
        $action->setEventWebhook($webhook);

        $ncco = $action->toNCCOArray();

        $this->assertSame('notify', $ncco['action']);
        $this->assertSame(['foo' => 'bar'], $ncco['payload']);
        $this->assertSame(['https://test.domain/events'], $ncco['eventUrl']);
        $this->assertSame('POST', $ncco['eventMethod']);
    }

    public function testJSONSerializesToCorrectStructure()
    {
        $webhook = new Webhook('https://test.domain/events');

        $action = new Notify(['foo' => 'bar'], $webhook);
        $action->setEventWebhook($webhook);

        $ncco = $action->jsonSerialize();

        $this->assertSame('notify', $ncco['action']);
        $this->assertSame(['foo' => 'bar'], $ncco['payload']);
        $this->assertSame(['https://test.domain/events'], $ncco['eventUrl']);
        $this->assertSame('POST', $ncco['eventMethod']);
    }

    public function testCanAddToPayload()
    {
        $webhook = new Webhook('https://test.domain/events');
        $action = new Notify(['foo' => 'bar'], $webhook);
        $action->addToPayload('baz', 'biff');

        $this->assertSame(['foo' => 'bar', 'baz' => 'biff'], $action->getPayload());
    }

    public function testThrowsExceptionWhenMissingEventURL()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Must supply at least an eventUrl for Notify NCCO');

        Notify::factory(['foo' => 'bar'], []);
    }
}
