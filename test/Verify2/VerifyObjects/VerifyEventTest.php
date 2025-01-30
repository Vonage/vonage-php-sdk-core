<?php

declare(strict_types=1);

namespace VonageTest\Verify2\VerifyObjects;

use PHPUnit\Framework\TestCase;
use Vonage\Verify2\VerifyObjects\VerifyEvent;

class VerifyEventTest extends TestCase
{
    public function testConstructorInitializesData()
    {
        $data = ['eventType' => 'completed', 'timestamp' => '2025-01-01T00:00:00Z'];
        $event = new VerifyEvent($data);

        $this->assertSame($data, $event->toArray());
    }

    public function testPropertyGetAndSet()
    {
        $event = new VerifyEvent(['eventType' => 'started']);
        $event->timestamp = '2025-01-01T00:00:00Z';

        $this->assertSame('started', $event->eventType);
        $this->assertSame('2025-01-01T00:00:00Z', $event->timestamp);
        $this->assertNull($event->unknownProperty);
    }

    public function testPropertyIsset()
    {
        $event = new VerifyEvent(['eventType' => 'completed']);

        $this->assertTrue(isset($event->eventType));
        $this->assertFalse(isset($event->timestamp));
    }

    public function testFromArrayHydratesData()
    {
        $data = ['eventType' => 'completed', 'timestamp' => '2025-01-01T00:00:00Z'];
        $event = new VerifyEvent([]);
        $event->fromArray($data);

        $this->assertSame($data, $event->toArray());
    }

    public function testToArrayReturnsData()
    {
        $data = ['eventType' => 'started', 'timestamp' => '2025-01-01T00:00:00Z'];
        $event = new VerifyEvent($data);

        $this->assertSame($data, $event->toArray());
    }

    public function testChainingWhenSettingProperties()
    {
        $event = new VerifyEvent([]);
        $result = $event->__set('eventType', 'completed');

        $this->assertInstanceOf(VerifyEvent::class, $result);
    }
}
