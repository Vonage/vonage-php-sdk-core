<?php
declare(strict_types=1);

namespace VonageTest\Voice\NCCO\Action;

use PHPUnit\Framework\TestCase;
use Vonage\Voice\NCCO\Action\Conversation;
use Vonage\Voice\Webhook;

class ConversationTest extends TestCase
{
    public function testSimpleSetup()
    {
        $expected = [
            'action' => 'conversation',
            'name' => 'my-conversation'
        ];

        $action = new Conversation('my-conversation');

        $this->assertSame($expected, $action->toNCCOArray());
    }

    public function testCanSetMusicOnHold()
    {
        $action = new Conversation('my-conversation');
        $action->setMusicOnHoldUrl('https://test.domain/hold.mp3');

        $data = $action->toNCCOArray();
        $this->assertSame(['https://test.domain/hold.mp3'], $data['musicOnHoldUrl']);

        $action = Conversation::factory('my-conversation', ['musicOnHoldUrl' => 'https://test.domain/hold.mp3']);
        $this->assertSame(['https://test.domain/hold.mp3'], $data['musicOnHoldUrl']);
    }

    public function testCanAddIndividualSpeakers()
    {
        $action = new Conversation('my-conversation');
        $action->addCanSpeak('6a4d6af0-55a6-4667-be90-8614e4c8e83c');

        $ncco = $action->toNCCOArray();

        $this->assertSame(['6a4d6af0-55a6-4667-be90-8614e4c8e83c'], $ncco['canSpeak']);
    }

    public function testCanAddIndividualListeners()
    {
        $action = new Conversation('my-conversation');
        $action->addCanHear('6a4d6af0-55a6-4667-be90-8614e4c8e83c');

        $ncco = $action->toNCCOArray();

        $this->assertSame(['6a4d6af0-55a6-4667-be90-8614e4c8e83c'], $ncco['canHear']);
    }

    public function testJsonSerializesToCorrectStructure()
    {
        $expected = [
            'action' => 'conversation',
            'name' => 'my-conversation',
            'startOnEnter' => 'true',
            'endOnExit' => 'false',
            'record' => 'false',
        ];

        $action = new Conversation('my-conversation');
        $action->setStartOnEnter(true);
        $action->setEndOnExit(false);
        $action->setRecord(false);

        $data = $action->jsonSerialize();

        $this->assertSame($expected, $data);
    }

    public function testCanSetRecordEventUrl()
    {
        $action = new Conversation('my-conversation');
        $action->setRecord(true);
        $action->setEventWebhook(new Webhook('https://test.domain/events'));

        $data = $action->toNCCOArray();

        $this->assertSame(['https://test.domain/events'], $data['eventUrl']);
        $this->assertSame('POST', $data['eventMethod']);
    }

    public function testWebhookSetInFactory()
    {
        $expected = [
            'action' => 'conversation',
            'name' => 'my-conversation',
            'eventUrl' => ['https://test.domain/events'],
            'eventMethod' => 'GET',
        ];

        $action = Conversation::factory($expected['name'], $expected);

        $this->assertTrue($action->getEventWebhook() instanceof Webhook);
        $this->assertSame($expected['eventUrl'][0], $action->getEventWebhook()->getUrl());
        $this->assertSame($expected['eventMethod'], $action->getEventWebhook()->getMethod());
    }

    public function testWebhookSetInFactoryWithoutMethod()
    {
        $expected = [
            'action' => 'conversation',
            'name' => 'my-conversation',
            'eventUrl' => ['https://test.domain/events'],
        ];

        $action = Conversation::factory($expected['name'], $expected);

        $this->assertTrue($action->getEventWebhook() instanceof Webhook);
        $this->assertSame($expected['eventUrl'][0], $action->getEventWebhook()->getUrl());
        $this->assertSame('POST', $action->getEventWebhook()->getMethod());
    }

    public function testWebhookSetInFactoryWithStringEventUrl()
    {
        $expected = [
            'action' => 'conversation',
            'name' => 'my-conversation',
            'eventUrl' => 'https://test.domain/events',
        ];

        $action = Conversation::factory($expected['name'], $expected);

        $this->assertTrue($action->getEventWebhook() instanceof Webhook);
        $this->assertSame($expected['eventUrl'], $action->getEventWebhook()->getUrl());
        $this->assertSame('POST', $action->getEventWebhook()->getMethod());
    }
}
