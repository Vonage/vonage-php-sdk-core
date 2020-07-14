<?php
declare(strict_types=1);

namespace NexmoTest\Voice\NCCO\Action;

use PHPUnit\Framework\TestCase;
use Nexmo\Voice\NCCO\Action\Conversation;

class ConversationTest extends TestCase
{
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
        $data = $action->jsonSerialize();

        $this->assertSame($expected, $data);
    }
}
