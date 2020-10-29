<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\SMS\Webhook;

use Exception;
use InvalidArgumentException;
use Laminas\Diactoros\Request\Serializer;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Vonage\SMS\Webhook\Factory;
use Vonage\SMS\Webhook\InboundSMS;

use function file_get_contents;
use function json_decode;
use function parse_str;

class InboundSMSTest extends TestCase
{
    public function testCanCreateFromFormPostServerRequest(): void
    {
        $expected = $this->getQueryStringFromRequest('inbound');
        $request = $this->getServerRequest('inbound');
        $inboundSMS = Factory::createFromRequest($request);

        self::assertSame($expected['msisdn'], $inboundSMS->getMsisdn());
        self::assertSame($expected['msisdn'], $inboundSMS->getFrom());
        self::assertSame($expected['to'], $inboundSMS->getTo());
        self::assertSame($expected['messageId'], $inboundSMS->getMessageId());
        self::assertSame($expected['text'], $inboundSMS->getText());
        self::assertSame($expected['type'], $inboundSMS->getType());
        self::assertSame($expected['keyword'], $inboundSMS->getKeyword());
        self::assertSame($expected['message-timestamp'], $inboundSMS->getMessageTimestamp()->format('Y-m-d H:i:s'));
        self::assertSame((int)$expected['timestamp'], $inboundSMS->getTimestamp());
        self::assertSame($expected['nonce'], $inboundSMS->getNonce());
        self::assertSame($expected['sig'], $inboundSMS->getSignature());
    }

    public function testCanCreateIncomingBinaryMessage(): void
    {
        $expected = $this->getQueryStringFromRequest('inbound-binary');
        $request = $this->getServerRequest('inbound-binary');
        $inboundSMS = Factory::createFromRequest($request);

        self::assertSame($expected['msisdn'], $inboundSMS->getMsisdn());
        self::assertSame($expected['msisdn'], $inboundSMS->getFrom());
        self::assertSame($expected['to'], $inboundSMS->getTo());
        self::assertSame($expected['messageId'], $inboundSMS->getMessageId());
        self::assertSame($expected['text'], $inboundSMS->getText());
        self::assertSame($expected['type'], $inboundSMS->getType());
        self::assertSame($expected['keyword'], $inboundSMS->getKeyword());
        self::assertSame($expected['data'], $inboundSMS->getData());
        self::assertSame($expected['udh'], $inboundSMS->getUdh());
    }

    public function testCanCreateFromConcatMessageFormPostServerRequest(): void
    {
        $expected = $this->getQueryStringFromRequest('inbound-long');
        $request = $this->getServerRequest('inbound-long');
        $inboundSMS = Factory::createFromRequest($request);

        self::assertSame($expected['msisdn'], $inboundSMS->getMsisdn());
        self::assertSame($expected['msisdn'], $inboundSMS->getFrom());
        self::assertSame($expected['to'], $inboundSMS->getTo());
        self::assertSame($expected['messageId'], $inboundSMS->getMessageId());
        self::assertSame($expected['text'], $inboundSMS->getText());
        self::assertSame($expected['type'], $inboundSMS->getType());
        self::assertSame($expected['keyword'], $inboundSMS->getKeyword());
        self::assertSame($expected['message-timestamp'], $inboundSMS->getMessageTimestamp()->format('Y-m-d H:i:s'));
        self::assertSame((bool)$expected['concat'], $inboundSMS->getConcat());
        self::assertSame((int)$expected['concat-part'], $inboundSMS->getConcatPart());
        self::assertSame($expected['concat-ref'], $inboundSMS->getConcatRef());
        self::assertSame((int)$expected['concat-total'], $inboundSMS->getConcatTotal());
    }

    public function testThrowRuntimeExceptionWhenInvalidRequestDetected(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Invalid method for incoming webhook");
        $request = new ServerRequest([], [], '/', 'DELETE');

        Factory::createFromRequest($request);
    }

    public function testCanCreateFromJSONPostServerRequest(): void
    {
        $expected = $this->getBodyFromRequest('json');
        $request = $this->getServerRequest('json');
        $inboundSMS = Factory::createFromRequest($request);

        self::assertSame($expected['msisdn'], $inboundSMS->getMsisdn());
        self::assertSame($expected['to'], $inboundSMS->getTo());
        self::assertSame($expected['messageId'], $inboundSMS->getMessageId());
        self::assertSame($expected['text'], $inboundSMS->getText());
        self::assertSame($expected['type'], $inboundSMS->getType());
        self::assertSame($expected['keyword'], $inboundSMS->getKeyword());
        self::assertSame($expected['message-timestamp'], $inboundSMS->getMessageTimestamp()->format('Y-m-d H:i:s'));
        self::assertSame((int)$expected['timestamp'], $inboundSMS->getTimestamp());
        self::assertSame($expected['nonce'], $inboundSMS->getNonce());
        self::assertSame($expected['sig'], $inboundSMS->getSignature());
    }

    /**
     * @throws Exception
     */
    public function testCanCreateFromRawArray(): void
    {
        $expected = $this->getQueryStringFromRequest('inbound');
        $inboundSMS = new InboundSMS($expected);

        self::assertSame($expected['msisdn'], $inboundSMS->getMsisdn());
        self::assertSame($expected['msisdn'], $inboundSMS->getFrom());
        self::assertSame($expected['to'], $inboundSMS->getTo());
        self::assertSame($expected['messageId'], $inboundSMS->getMessageId());
        self::assertSame($expected['text'], $inboundSMS->getText());
        self::assertSame($expected['type'], $inboundSMS->getType());
        self::assertSame($expected['keyword'], $inboundSMS->getKeyword());
        self::assertSame($expected['message-timestamp'], $inboundSMS->getMessageTimestamp()->format('Y-m-d H:i:s'));
        self::assertSame((int)$expected['timestamp'], $inboundSMS->getTimestamp());
        self::assertSame($expected['nonce'], $inboundSMS->getNonce());
        self::assertSame($expected['sig'], $inboundSMS->getSignature());
    }

    public function testCanCreateFromGetWithBodyServerRequest(): void
    {
        $expected = $this->getQueryStringFromRequest('inbound');
        $request = $this->getServerRequest('inbound');
        $inboundSMS = Factory::createFromRequest($request);

        self::assertSame($expected['msisdn'], $inboundSMS->getMsisdn());
        self::assertSame($expected['to'], $inboundSMS->getTo());
        self::assertSame($expected['messageId'], $inboundSMS->getMessageId());
        self::assertSame($expected['text'], $inboundSMS->getText());
        self::assertSame($expected['type'], $inboundSMS->getType());
        self::assertSame($expected['keyword'], $inboundSMS->getKeyword());
        self::assertSame($expected['message-timestamp'], $inboundSMS->getMessageTimestamp()->format('Y-m-d H:i:s'));
        self::assertSame((int)$expected['timestamp'], $inboundSMS->getTimestamp());
        self::assertSame($expected['nonce'], $inboundSMS->getNonce());
        self::assertSame($expected['sig'], $inboundSMS->getSignature());
    }

    /**
     * @throws Exception
     */
    public function testThrowsExceptionWithInvalidRequest(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Incoming SMS missing required data `msisdn`');

        $request = $this->getServerRequest('invalid')->getQueryParams();

        new InboundSMS($request);
    }

    /**
     * @param string $requestName
     *
     * @return mixed
     */
    protected function getQueryStringFromRequest(string $requestName)
    {
        $text = file_get_contents(__DIR__ . '/../requests/' . $requestName . '.txt');
        $request = Serializer::fromString($text);

        parse_str($request->getUri()->getQuery(), $query);

        return $query;
    }

    /**
     * @param string $requestName
     *
     * @return mixed
     */
    protected function getBodyFromRequest(string $requestName)
    {
        $text = file_get_contents(__DIR__ . '/../requests/' . $requestName . '.txt');
        $request = Serializer::fromString($text);

        return json_decode($request->getBody()->getContents(), true);
    }

    protected function getServerRequest(string $requestName): ServerRequest
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
