<?php

declare(strict_types=1);

namespace VonageTest\Verify2\VerifyObjects;

use PHPUnit\Framework\TestCase;
use Vonage\Verify2\VerifyObjects\VerifySilentAuthEvent;

class VerifySilentAuthEventTest extends TestCase
{
    public function testConstructorInitializesData()
    {
        $data = ['eventType' => 'completed', 'timestamp' => '2025-01-01T00:00:00Z'];
        $event = new VerifySilentAuthEvent($data);

        $this->assertSame($data, $event->toArray());
    }

    public function testPropertyGetAndSet()
    {
        $event = new VerifySilentAuthEvent(['eventType' => 'started']);
        $event->timestamp = '2025-01-01T00:00:00Z';

        $this->assertSame('started', $event->eventType);
        $this->assertSame('2025-01-01T00:00:00Z', $event->timestamp);
        $this->assertNull($event->unknownProperty);
    }

    public function testPropertyIsset()
    {
        $event = new VerifySilentAuthEvent(['eventType' => 'completed']);

        $this->assertTrue(isset($event->eventType));
        $this->assertFalse(isset($event->timestamp));
    }

    public function testFromArrayHydratesData()
    {
        $data = ['eventType' => 'completed', 'timestamp' => '2025-01-01T00:00:00Z'];
        $event = new VerifySilentAuthEvent([]);
        $event->fromArray($data);

        $this->assertSame($data, $event->toArray());
    }

    public function testToArrayReturnsData()
    {
        $data = ['eventType' => 'started', 'timestamp' => '2025-01-01T00:00:00Z'];
        $event = new VerifySilentAuthEvent($data);

        $this->assertSame($data, $event->toArray());
    }

    public function testChainingWhenSettingProperties()
    {
        $event = new VerifySilentAuthEvent([]);
        $result = $event->__set('eventType', 'completed');

        $this->assertInstanceOf(VerifySilentAuthEvent::class, $result);
    }
}
