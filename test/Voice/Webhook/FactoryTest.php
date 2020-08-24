<?php
declare(strict_types=1);

namespace VonageTest\Voice;

use InvalidArgumentException;
use Vonage\Voice\Webhook\Error;
use Vonage\Voice\Webhook\Event;
use Vonage\Voice\Webhook\Input;
use Vonage\Voice\Webhook\Answer;
use Vonage\Voice\Webhook\Record;
use PHPUnit\Framework\TestCase;
use Vonage\Voice\Webhook\Factory;
use Vonage\Voice\Webhook\Transfer;
use Zend\Diactoros\ServerRequest;
use Vonage\Voice\Webhook\Notification;
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

    public function testCanGenerateAnAnswerWebhook()
    {
        $request = $this->getRequest('answer-get');
        $expected = $this->getRequest('answer-get')->getQueryParams();

        /** @var Answer $answer */
        $answer = Factory::createFromRequest($request);

        $this->assertTrue($answer instanceof Answer);
        $this->assertSame($expected['conversation_uuid'], $answer->getConversationUuid());
        $this->assertSame($expected['uuid'], $answer->getUuid());
        $this->assertSame($expected['to'], $answer->getTo());
        $this->assertSame($expected['from'], $answer->getFrom());
    }

    public function testCanGenerateARecordingWebhook()
    {
        $request = $this->getRequest('recording-get');
        $expected = $this->getRequest('recording-get')->getQueryParams();

        /** @var Record $record */
        $record = Factory::createFromRequest($request);

        $this->assertTrue($record instanceof Record);
        $this->assertSame($expected['conversation_uuid'], $record->getConversationUuid());
        $this->assertEquals(new \DateTimeImmutable($expected['end_time']), $record->getEndTime());
        $this->assertSame($expected['recording_url'], $record->getRecordingUrl());
        $this->assertSame($expected['recording_uuid'], $record->getRecordingUuid());
        $this->assertSame((int) $expected['size'], $record->getSize());
        $this->assertEquals(new \DateTimeImmutable($expected['start_time']), $record->getStartTime());
        $this->assertEquals(new \DateTimeImmutable($expected['timestamp']), $record->getTimestamp());
    }

    public function testCanGenerateAnErrorWebhook()
    {
        $request = $this->getRequest('error-get');
        $expected = $this->getRequest('error-get')->getQueryParams();

        /** @var Error $error */
        $error = Factory::createFromRequest($request);

        $this->assertTrue($error instanceof Error);
        $this->assertSame($expected['conversation_uuid'], $error->getConversationUuid());
        $this->assertSame($expected['reason'], $error->getReason());
        $this->assertEquals(new \DateTimeImmutable($expected['timestamp']), $error->getTimestamp());
    }

    public function testCanGenerateANotificationGetWebhook()
    {
        $request = $this->getRequest('event-get-notify');
        $expected = $this->getRequest('event-get-notify')->getQueryParams();

        /** @var Notification $notification */
        $notification = Factory::createFromRequest($request);

        $this->assertTrue($notification instanceof Notification);
        $this->assertSame($expected['conversation_uuid'], $notification->getConversationUuid());
        $this->assertSame(json_decode($expected['payload'], true), $notification->getPayload());
        $this->assertEquals(new \DateTimeImmutable($expected['timestamp']), $notification->getTimestamp());
    }

    public function testCanGenerateANotificationPostWebhook()
    {
        $request = $this->getRequest('event-post-notify');
        $expected = json_decode($this->getRequest('event-post-notify')->getBody()->getContents(), true);

        /** @var Notification $notification */
        $notification = Factory::createFromRequest($request);

        $this->assertTrue($notification instanceof Notification);
        $this->assertSame($expected['conversation_uuid'], $notification->getConversationUuid());
        $this->assertSame($expected['payload'], $notification->getPayload());
        $this->assertEquals(new \DateTimeImmutable($expected['timestamp']), $notification->getTimestamp());
    }

    public function testCanGenerateDtmfInputFromGetWebhook()
    {
        $request = $this->getRequest('dtmf-get');
        $expected = $this->getRequest('dtmf-get')->getQueryParams();

        /** @var Input $input */
        $input = Factory::createFromRequest($request);

        $this->assertTrue($input instanceof Input);
        $this->assertSame(json_decode($expected['speech'], true), $input->getSpeech());
        $this->assertSame(json_decode($expected['dtmf'], true), $input->getDtmf());
        $this->assertSame($expected['from'], $input->getFrom());
        $this->assertSame($expected['to'], $input->getTo());
        $this->assertSame($expected['uuid'], $input->getUuid());
        $this->assertSame($expected['conversation_uuid'], $input->getConversationUuid());
        $this->assertEquals(new \DateTimeImmutable($expected['timestamp']), $input->getTimestamp());
    }

    public function testCanGenerateDtmfInputFromPostWebhook()
    {
        $request = $this->getRequest('dtmf-post');
        $expected = json_decode($this->getRequest('dtmf-post')->getBody()->getContents(), true);

        /** @var Input $input */
        $input = Factory::createFromRequest($request);

        $this->assertTrue($input instanceof Input);
        $this->assertSame($expected['speech'], $input->getSpeech());
        $this->assertSame($expected['dtmf'], $input->getDtmf());
        $this->assertSame($expected['from'], $input->getFrom());
        $this->assertSame($expected['to'], $input->getTo());
        $this->assertSame($expected['uuid'], $input->getUuid());
        $this->assertSame($expected['conversation_uuid'], $input->getConversationUuid());
        $this->assertEquals(new \DateTimeImmutable($expected['timestamp']), $input->getTimestamp());
    }

    public function testThrowsExceptionOnUnknownWebhookData()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to detect incoming webhook type');

        Factory::createFromArray(['foo' => 'bar']);
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
