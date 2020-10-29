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

        self::assertSame(['foo' => 'bar'], $action->getPayload());
        self::assertSame($webhook, $action->getEventWebhook());
    }

    public function testCanGenerateFromFactory(): void
    {
        $data = [
            'action' => 'notify',
            'payload' => ['foo' => 'bar'],
            'eventUrl' => 'https://test.domain/events',
        ];

        $action = Notify::factory(['foo' => 'bar'], $data);

        self::assertSame(['foo' => 'bar'], $action->getPayload());
        self::assertSame('https://test.domain/events', $action->getEventWebhook()->getUrl());
        self::assertSame('POST', $action->getEventWebhook()->getMethod());
    }

    public function testGeneratesCorrectNCCOArray(): void
    {
        $webhook = new Webhook('https://test.domain/events');

        $action = new Notify(['foo' => 'bar'], $webhook);
        $action->setEventWebhook($webhook);

        $ncco = $action->toNCCOArray();

        self::assertSame('notify', $ncco['action']);
        self::assertSame(['foo' => 'bar'], $ncco['payload']);
        self::assertSame(['https://test.domain/events'], $ncco['eventUrl']);
        self::assertSame('POST', $ncco['eventMethod']);
    }

    public function testJSONSerializesToCorrectStructure(): void
    {
        $webhook = new Webhook('https://test.domain/events');
        $ncco = (new Notify(['foo' => 'bar'], $webhook))->setEventWebhook($webhook)->jsonSerialize();

        self::assertSame('notify', $ncco['action']);
        self::assertSame(['foo' => 'bar'], $ncco['payload']);
        self::assertSame(['https://test.domain/events'], $ncco['eventUrl']);
        self::assertSame('POST', $ncco['eventMethod']);
    }

    public function testCanAddToPayload(): void
    {
        $webhook = new Webhook('https://test.domain/events');
        $action = (new Notify(['foo' => 'bar'], $webhook))->addToPayload('baz', 'biff');

        self::assertSame(['foo' => 'bar', 'baz' => 'biff'], $action->getPayload());
    }

    public function testThrowsExceptionWhenMissingEventURL(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Must supply at least an eventUrl for Notify NCCO');

        Notify::factory(['foo' => 'bar'], []);
    }
}
