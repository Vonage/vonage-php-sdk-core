<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace VonageTest\Voice\NCCO\Action;

use PHPUnit\Framework\TestCase;
use Vonage\Voice\NCCO\Action\Conversation;
use Vonage\Voice\Webhook;

class ConversationTest extends TestCase
{
    public function testSimpleSetup(): void
    {
        self::assertSame([
            'action' => 'conversation',
            'name' => 'my-conversation'
        ], (new Conversation('my-conversation'))->toNCCOArray());
    }

    public function testCanSetMusicOnHold(): void
    {
        $action = new Conversation('my-conversation');
        $action->setMusicOnHoldUrl('https://test.domain/hold.mp3');
        $data = $action->toNCCOArray();

        self::assertSame(['https://test.domain/hold.mp3'], $data['musicOnHoldUrl']);

        Conversation::factory('my-conversation', ['musicOnHoldUrl' => 'https://test.domain/hold.mp3']);

        self::assertSame(['https://test.domain/hold.mp3'], $data['musicOnHoldUrl']);
    }

    public function testCanAddIndividualSpeakers(): void
    {
        $uuid = '6a4d6af0-55a6-4667-be90-8614e4c8e83c';

        self::assertSame([$uuid], (new Conversation('my-conversation'))
            ->addCanSpeak($uuid)
            ->toNCCOArray()['canSpeak']);
    }

    public function testCanAddIndividualListeners(): void
    {
        $uuid = '6a4d6af0-55a6-4667-be90-8614e4c8e83c';

        self::assertSame([$uuid], (new Conversation('my-conversation'))
            ->addCanHear($uuid)
            ->toNCCOArray()['canHear']);
    }

    public function testJsonSerializesToCorrectStructure(): void
    {
        self::assertSame([
            'action' => 'conversation',
            'name' => 'my-conversation',
            'startOnEnter' => 'true',
            'endOnExit' => 'false',
            'record' => 'false',
        ], (new Conversation('my-conversation'))
            ->setStartOnEnter(true)
            ->setEndOnExit(false)
            ->setRecord(false)
            ->jsonSerialize());
    }

    public function testCanSetRecordEventUrl(): void
    {
        $data = (new Conversation('my-conversation'))
            ->setRecord(true)
            ->setEventWebhook(new Webhook('https://test.domain/events'))
            ->toNCCOArray();

        self::assertSame(['https://test.domain/events'], $data['eventUrl']);
        self::assertSame('POST', $data['eventMethod']);
    }

    public function testWebhookSetInFactory(): void
    {
        $expected = [
            'action' => 'conversation',
            'name' => 'my-conversation',
            'eventUrl' => ['https://test.domain/events'],
            'eventMethod' => 'GET',
        ];

        $action = Conversation::factory($expected['name'], $expected);

        self::assertInstanceOf(Webhook::class, $action->getEventWebhook());
        self::assertSame($expected['eventUrl'][0], $action->getEventWebhook()->getUrl());
        self::assertSame($expected['eventMethod'], $action->getEventWebhook()->getMethod());
    }

    public function testWebhookSetInFactoryWithoutMethod(): void
    {
        $expected = [
            'action' => 'conversation',
            'name' => 'my-conversation',
            'eventUrl' => ['https://test.domain/events'],
        ];

        $action = Conversation::factory($expected['name'], $expected);

        self::assertInstanceOf(Webhook::class, $action->getEventWebhook());
        self::assertSame($expected['eventUrl'][0], $action->getEventWebhook()->getUrl());
        self::assertSame('POST', $action->getEventWebhook()->getMethod());
    }

    public function testWebhookSetInFactoryWithStringEventUrl(): void
    {
        $expected = [
            'action' => 'conversation',
            'name' => 'my-conversation',
            'eventUrl' => 'https://test.domain/events',
        ];

        $action = Conversation::factory($expected['name'], $expected);

        self::assertInstanceOf(Webhook::class, $action->getEventWebhook());
        self::assertSame($expected['eventUrl'], $action->getEventWebhook()->getUrl());
        self::assertSame('POST', $action->getEventWebhook()->getMethod());
    }
}
