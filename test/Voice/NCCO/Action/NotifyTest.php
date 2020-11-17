<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Voice\NCCO\Action;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Vonage\Voice\NCCO\Action\Notify;
use Vonage\Voice\Webhook;

class NotifyTest extends TestCase
{
    public function testCanSetAdditionalInformation(): void
    {
        $webhook = new Webhook('https://test.domain/events');
        $action = (new Notify(['foo' => 'bar'], $webhook))->setEventWebhook($webhook);

        $this->assertSame(['foo' => 'bar'], $action->getPayload());
        $this->assertSame($webhook, $action->getEventWebhook());
    }

    public function testCanGenerateFromFactory(): void
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

    public function testGeneratesCorrectNCCOArray(): void
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

    public function testJSONSerializesToCorrectStructure(): void
    {
        $webhook = new Webhook('https://test.domain/events');
        $ncco = (new Notify(['foo' => 'bar'], $webhook))->setEventWebhook($webhook)->jsonSerialize();

        $this->assertSame('notify', $ncco['action']);
        $this->assertSame(['foo' => 'bar'], $ncco['payload']);
        $this->assertSame(['https://test.domain/events'], $ncco['eventUrl']);
        $this->assertSame('POST', $ncco['eventMethod']);
    }

    public function testCanAddToPayload(): void
    {
        $webhook = new Webhook('https://test.domain/events');
        $action = (new Notify(['foo' => 'bar'], $webhook))->addToPayload('baz', 'biff');

        $this->assertSame(['foo' => 'bar', 'baz' => 'biff'], $action->getPayload());
    }

    public function testThrowsExceptionWhenMissingEventURL(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Must supply at least an eventUrl for Notify NCCO');

        Notify::factory(['foo' => 'bar'], []);
    }
}
