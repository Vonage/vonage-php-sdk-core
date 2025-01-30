<?php

declare(strict_types=1);

namespace VonageTest\Verify2\VerifyObjects;

use PHPUnit\Framework\TestCase;
use Vonage\Verify2\VerifyObjects\VerifyWhatsAppInteractiveEvent;

class VerifyWhatsAppInteractiveEventTest extends TestCase
{
    public function testConstructorInitializesData()
    {
        $data = ['eventType' => 'completed', 'timestamp' => '2025-01-01T00:00:00Z'];
        $event = new VerifyWhatsAppInteractiveEvent($data);

        $this->assertSame($data, $event->toArray());
    }

    public function testPropertyGetAndSet()
    {
        $event = new VerifyWhatsAppInteractiveEvent(['eventType' => 'started']);
        $event->timestamp = '2025-01-01T00:00:00Z';

        $this->assertSame('started', $event->eventType);
        $this->assertSame('2025-01-01T00:00:00Z', $event->timestamp);
        $this->assertNull($event->unknownProperty);
    }

    public function testPropertyIsset()
    {
        $event = new VerifyWhatsAppInteractiveEvent(['eventType' => 'completed']);

        $this->assertTrue(isset($event->eventType));
        $this->assertFalse(isset($event->timestamp));
    }

    public function testFromArrayHydratesData()
    {
        $data = ['eventType' => 'completed', 'timestamp' => '2025-01-01T00:00:00Z'];
        $event = new VerifyWhatsAppInteractiveEvent([]);
        $event->fromArray($data);

        $this->assertSame($data, $event->toArray());
    }

    public function testToArrayReturnsData()
    {
        $data = ['eventType' => 'started', 'timestamp' => '2025-01-01T00:00:00Z'];
        $event = new VerifyWhatsAppInteractiveEvent($data);

        $this->assertSame($data, $event->toArray());
    }

    public function testChainingWhenSettingProperties()
    {
        $event = new VerifyWhatsAppInteractiveEvent([]);
        $result = $event->__set('eventType', 'completed');

        $this->assertInstanceOf(VerifyWhatsAppInteractiveEvent::class, $result);
    }
}
