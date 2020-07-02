<?php
declare(strict_types=1);

namespace NexmoTest\Voice;

use Nexmo\Voice\Webhook\Event;
use PHPUnit\Framework\TestCase;
use Nexmo\Voice\Webhook\Factory;
use Nexmo\Voice\Webhook\Transfer;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Request\Serializer;

class FactoryTest extends TestCase
{
    public function testCanGenerateStartedEvent()
    {
        $request = $this->getRequest('event-post-started');
        $expected = json_decode($this->getRequest('event-post-started')->getBody()->getContents(), true);
        $event = Factory::createFromRequest($request);

        $this->assertTrue($event instanceof Event);
        $this->assertSame($expected['status'], $event->getStatus());
        $this->assertSame($expected['from'], $event->getFrom());
        $this->assertSame($expected['to'], $event->getTo());
        $this->assertSame($expected['uuid'], $event->getUuid());
        $this->assertSame($expected['conversation_uuid'], $event->getConversationUuid());
        $this->assertSame($expected['direction'], $event->getDirection());
        $this->assertEquals(new \DateTime($expected['timestamp']), $event->getTimestamp());

        $this->assertNull($event->getDuration());
        $this->assertNull($event->getPrice());
    }

    public function testCanGenerateRingingEvent()
    {
        $request = $this->getRequest('event-post-ringing');
        $expected = json_decode($this->getRequest('event-post-ringing')->getBody()->getContents(), true);
        $event = Factory::createFromRequest($request);

        $this->assertTrue($event instanceof Event);
        $this->assertSame($expected['status'], $event->getStatus());
        $this->assertSame($expected['from'], $event->getFrom());
        $this->assertSame($expected['to'], $event->getTo());
        $this->assertSame($expected['uuid'], $event->getUuid());
        $this->assertSame($expected['conversation_uuid'], $event->getConversationUuid());
        $this->assertSame($expected['direction'], $event->getDirection());
        $this->assertEquals(new \DateTime($expected['timestamp']), $event->getTimestamp());

        $this->assertNull($event->getDuration());
        $this->assertNull($event->getPrice());
    }

    public function testCanGenerateAnsweredEvent()
    {
        $request = $this->getRequest('event-post-answered');
        $expected = json_decode($this->getRequest('event-post-answered')->getBody()->getContents(), true);
        $event = Factory::createFromRequest($request);

        $this->assertTrue($event instanceof Event);
        $this->assertSame($expected['status'], $event->getStatus());
        $this->assertSame($expected['from'], $event->getFrom());
        $this->assertSame($expected['to'], $event->getTo());
        $this->assertSame($expected['uuid'], $event->getUuid());
        $this->assertSame($expected['conversation_uuid'], $event->getConversationUuid());
        $this->assertSame($expected['direction'], $event->getDirection());
        $this->assertEquals(new \DateTime($expected['timestamp']), $event->getTimestamp());

        $this->assertNull($event->getStartTime());
        $this->assertNull($event->getRate());
    }

    public function testCanGenerateCompletedEvent()
    {
        $request = $this->getRequest('event-post-completed');
        $expected = json_decode($this->getRequest('event-post-completed')->getBody()->getContents(), true);
        $event = Factory::createFromRequest($request);

        $this->assertTrue($event instanceof Event);
        $this->assertSame($expected['status'], $event->getStatus());
        $this->assertSame($expected['from'], $event->getFrom());
        $this->assertSame($expected['to'], $event->getTo());
        $this->assertSame($expected['uuid'], $event->getUuid());
        $this->assertSame($expected['conversation_uuid'], $event->getConversationUuid());
        $this->assertSame($expected['direction'], $event->getDirection());
        $this->assertEquals(new \DateTime($expected['timestamp']), $event->getTimestamp());
        $this->assertSame($expected['network'], $event->getNetwork());
        $this->assertSame($expected['duration'], $event->getDuration());
        $this->assertEquals(new \DateTime($expected['start_time']), $event->getStartTime());
        $this->assertEquals(new \DateTime($expected['end_time']), $event->getEndTime());
        $this->assertSame($expected['rate'], $event->getRate());
        $this->assertSame($expected['price'], $event->getPrice());
    }

    public function testCanGenerateTransferWebhook()
    {
        $request = $this->getRequest('event-post-transfer');
        $expected = json_decode($this->getRequest('event-post-transfer')->getBody()->getContents(), true);
        $event = Factory::createFromRequest($request);

        $this->assertTrue($event instanceof Transfer);
        $this->assertSame($expected['conversation_uuid_from'], $event->getConversationUuidFrom());
        $this->assertSame($expected['conversation_uuid_to'], $event->getConversationUuidTo());
        $this->assertSame($expected['uuid'], $event->getUuid());
        $this->assertEquals(new \DateTimeImmutable($expected['timestamp']), $event->getTimestamp());
    }

    public function getRequest(string $requestName)
    {
        $text = file_get_contents(__DIR__ . '/../requests/' . $requestName . '.txt');
        $request = Serializer::fromString($text);
        parse_str($request->getUri()->getQuery(), $query);

        return new ServerRequest(
            [],
            [],
            $request->getHeader('Host')[0],
            $request->getMethod(),
            $request->getBody(),
            $request->getHeaders(),
            [],
            $query
        );
    }
}
