<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Voice\Webhook;

use DateTime;
use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use Laminas\Diactoros\Request\Serializer;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use Vonage\Voice\Webhook\Answer;
use Vonage\Voice\Webhook\Error;
use Vonage\Voice\Webhook\Event;
use Vonage\Voice\Webhook\Factory;
use Vonage\Voice\Webhook\Input;
use Vonage\Voice\Webhook\Notification;
use Vonage\Voice\Webhook\Record;
use Vonage\Voice\Webhook\Transfer;

class FactoryTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testCanGenerateStartedEvent(): void
    {
        $request = $this->getRequest('event-post-started');
        $expected = json_decode($this->getRequest('event-post-started')->getBody()->getContents(), true);
        $event = Factory::createFromRequest($request);

        self::assertInstanceOf(Event::class, $event);
        self::assertSame($expected['status'], $event->getStatus());
        self::assertSame($expected['from'], $event->getFrom());
        self::assertSame($expected['to'], $event->getTo());
        self::assertSame($expected['uuid'], $event->getUuid());
        self::assertSame($expected['conversation_uuid'], $event->getConversationUuid());
        self::assertSame($expected['direction'], $event->getDirection());
        self::assertEquals(new DateTime($expected['timestamp']), $event->getTimestamp());
        self::assertNull($event->getDuration());
        self::assertNull($event->getPrice());
    }

    /**
     * @throws Exception
     */
    public function testCanGenerateRingingEvent(): void
    {
        $request = $this->getRequest('event-post-ringing');
        $expected = json_decode($this->getRequest('event-post-ringing')->getBody()->getContents(), true);
        $event = Factory::createFromRequest($request);

        self::assertInstanceOf(Event::class, $event);
        self::assertSame($expected['status'], $event->getStatus());
        self::assertSame($expected['from'], $event->getFrom());
        self::assertSame($expected['to'], $event->getTo());
        self::assertSame($expected['uuid'], $event->getUuid());
        self::assertSame($expected['conversation_uuid'], $event->getConversationUuid());
        self::assertSame($expected['direction'], $event->getDirection());
        self::assertEquals(new DateTime($expected['timestamp']), $event->getTimestamp());
        self::assertNull($event->getDuration());
        self::assertNull($event->getPrice());
    }

    /**
     * @throws Exception
     */
    public function testCanGenerateAnsweredEvent(): void
    {
        $request = $this->getRequest('event-post-answered');
        $expected = json_decode($this->getRequest('event-post-answered')->getBody()->getContents(), true);
        $event = Factory::createFromRequest($request);

        self::assertInstanceOf(Event::class, $event);
        self::assertSame($expected['status'], $event->getStatus());
        self::assertSame($expected['from'], $event->getFrom());
        self::assertSame($expected['to'], $event->getTo());
        self::assertSame($expected['uuid'], $event->getUuid());
        self::assertSame($expected['conversation_uuid'], $event->getConversationUuid());
        self::assertSame($expected['direction'], $event->getDirection());
        self::assertEquals(new DateTime($expected['timestamp']), $event->getTimestamp());
        self::assertNull($event->getStartTime());
        self::assertNull($event->getRate());
    }

    /**
     * @throws Exception
     */
    public function testCanGenerateCompletedEvent(): void
    {
        $request = $this->getRequest('event-post-completed');
        $expected = json_decode($this->getRequest('event-post-completed')->getBody()->getContents(), true);
        $event = Factory::createFromRequest($request);

        self::assertInstanceOf(Event::class, $event);
        self::assertSame($expected['status'], $event->getStatus());
        self::assertSame($expected['from'], $event->getFrom());
        self::assertSame($expected['to'], $event->getTo());
        self::assertSame($expected['uuid'], $event->getUuid());
        self::assertSame($expected['conversation_uuid'], $event->getConversationUuid());
        self::assertSame($expected['direction'], $event->getDirection());
        self::assertEquals(new DateTime($expected['timestamp']), $event->getTimestamp());
        self::assertSame($expected['network'], $event->getNetwork());
        self::assertSame($expected['duration'], $event->getDuration());
        self::assertEquals(new DateTime($expected['start_time']), $event->getStartTime());
        self::assertEquals(new DateTime($expected['end_time']), $event->getEndTime());
        self::assertSame($expected['rate'], $event->getRate());
        self::assertSame($expected['price'], $event->getPrice());
    }

    /**
     * @throws Exception
     */
    public function testCanGenerateTransferWebhook(): void
    {
        $request = $this->getRequest('event-post-transfer');
        $expected = json_decode($this->getRequest('event-post-transfer')->getBody()->getContents(), true);
        $event = Factory::createFromRequest($request);

        self::assertInstanceOf(Transfer::class, $event);
        self::assertSame($expected['conversation_uuid_from'], $event->getConversationUuidFrom());
        self::assertSame($expected['conversation_uuid_to'], $event->getConversationUuidTo());
        self::assertSame($expected['uuid'], $event->getUuid());
        self::assertEquals(new DateTimeImmutable($expected['timestamp']), $event->getTimestamp());
    }

    public function testCanGenerateAnAnswerWebhook(): void
    {
        $request = $this->getRequest('answer-get');
        $expected = $this->getRequest('answer-get')->getQueryParams();

        /** @var Answer $answer */
        $answer = Factory::createFromRequest($request);

        self::assertInstanceOf(Answer::class, $answer);
        self::assertSame($expected['conversation_uuid'], $answer->getConversationUuid());
        self::assertSame($expected['uuid'], $answer->getUuid());
        self::assertSame($expected['to'], $answer->getTo());
        self::assertSame($expected['from'], $answer->getFrom());
    }

    /**
     * @throws Exception
     */
    public function testCanGenerateARecordingWebhook(): void
    {
        $request = $this->getRequest('recording-get');
        $expected = $this->getRequest('recording-get')->getQueryParams();

        /** @var Record $record */
        $record = Factory::createFromRequest($request);

        self::assertInstanceOf(Record::class, $record);
        self::assertSame($expected['conversation_uuid'], $record->getConversationUuid());
        self::assertEquals(new DateTimeImmutable($expected['end_time']), $record->getEndTime());
        self::assertSame($expected['recording_url'], $record->getRecordingUrl());
        self::assertSame($expected['recording_uuid'], $record->getRecordingUuid());
        self::assertSame((int)$expected['size'], $record->getSize());
        self::assertEquals(new DateTimeImmutable($expected['start_time']), $record->getStartTime());
        self::assertEquals(new DateTimeImmutable($expected['timestamp']), $record->getTimestamp());
    }

    /**
     * @throws Exception
     */
    public function testCanGenerateAnErrorWebhook(): void
    {
        $request = $this->getRequest('error-get');
        $expected = $this->getRequest('error-get')->getQueryParams();

        /** @var Error $error */
        $error = Factory::createFromRequest($request);

        self::assertInstanceOf(Error::class, $error);
        self::assertSame($expected['conversation_uuid'], $error->getConversationUuid());
        self::assertSame($expected['reason'], $error->getReason());
        self::assertEquals(new DateTimeImmutable($expected['timestamp']), $error->getTimestamp());
    }

    /**
     * @throws Exception
     */
    public function testCanGenerateANotificationGetWebhook(): void
    {
        $request = $this->getRequest('event-get-notify');
        $expected = $this->getRequest('event-get-notify')->getQueryParams();

        /** @var Notification $notification */
        $notification = Factory::createFromRequest($request);

        self::assertInstanceOf(Notification::class, $notification);
        self::assertSame($expected['conversation_uuid'], $notification->getConversationUuid());
        self::assertSame(json_decode($expected['payload'], true), $notification->getPayload());
        self::assertEquals(new DateTimeImmutable($expected['timestamp']), $notification->getTimestamp());
    }

    /**
     * @throws Exception
     */
    public function testCanGenerateANotificationPostWebhook(): void
    {
        $request = $this->getRequest('event-post-notify');
        $expected = json_decode($this->getRequest('event-post-notify')->getBody()->getContents(), true);

        /** @var Notification $notification */
        $notification = Factory::createFromRequest($request);

        self::assertInstanceOf(Notification::class, $notification);
        self::assertSame($expected['conversation_uuid'], $notification->getConversationUuid());
        self::assertSame($expected['payload'], $notification->getPayload());
        self::assertEquals(new DateTimeImmutable($expected['timestamp']), $notification->getTimestamp());
    }

    /**
     * @throws Exception
     */
    public function testCanGenerateDtmfInputFromGetWebhook(): void
    {
        $request = $this->getRequest('dtmf-get');
        $expected = $this->getRequest('dtmf-get')->getQueryParams();

        /** @var Input $input */
        $input = Factory::createFromRequest($request);

        self::assertInstanceOf(Input::class, $input);
        self::assertSame(json_decode($expected['speech'], true), $input->getSpeech());
        self::assertSame(json_decode($expected['dtmf'], true), $input->getDtmf());
        self::assertSame($expected['from'], $input->getFrom());
        self::assertSame($expected['to'], $input->getTo());
        self::assertSame($expected['uuid'], $input->getUuid());
        self::assertSame($expected['conversation_uuid'], $input->getConversationUuid());
        self::assertEquals(new DateTimeImmutable($expected['timestamp']), $input->getTimestamp());
    }

    /**
     * @throws Exception
     */
    public function testCanGenerateDtmfInputFromPostWebhook(): void
    {
        $request = $this->getRequest('dtmf-post');
        $expected = json_decode($this->getRequest('dtmf-post')->getBody()->getContents(), true);

        /** @var Input $input */
        $input = Factory::createFromRequest($request);

        self::assertInstanceOf(Input::class, $input);
        self::assertSame($expected['speech'], $input->getSpeech());
        self::assertSame($expected['dtmf'], $input->getDtmf());
        self::assertSame($expected['from'], $input->getFrom());
        self::assertSame($expected['to'], $input->getTo());
        self::assertSame($expected['uuid'], $input->getUuid());
        self::assertSame($expected['conversation_uuid'], $input->getConversationUuid());
        self::assertEquals(new DateTimeImmutable($expected['timestamp']), $input->getTimestamp());
    }

    /**
     * @throws Exception
     */
    public function testThrowsExceptionOnUnknownWebhookData(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to detect incoming webhook type');

        Factory::createFromArray(['foo' => 'bar']);
    }

    /**
     * @param string $requestName
     * @return ServerRequest
     */
    public function getRequest(string $requestName): ServerRequest
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
