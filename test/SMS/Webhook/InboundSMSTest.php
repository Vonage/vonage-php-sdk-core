<?php
declare(strict_types=1);

namespace VonageTest\SMS\Webhook;

use RuntimeException;
use InvalidArgumentException;
use Vonage\SMS\Webhook\Factory;
use PHPUnit\Framework\TestCase;
use Vonage\SMS\Webhook\InboundSMS;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Request\Serializer;
use Zend\Diactoros\ServerRequestFactory;

class InboundSMSTest extends TestCase
{
    public function testCanCreateFromFormPostServerRequest()
    {
        $expected = $this->getQueryStringFromRequest('inbound');

        $request = $this->getServerRequest('inbound', 'GET');
        $inboundSMS = Factory::createFromRequest($request);

        $this->assertSame($expected['msisdn'], $inboundSMS->getMsisdn());
        $this->assertSame($expected['msisdn'], $inboundSMS->getFrom());
        $this->assertSame($expected['to'], $inboundSMS->getTo());
        $this->assertSame($expected['messageId'], $inboundSMS->getMessageId());
        $this->assertSame($expected['text'], $inboundSMS->getText());
        $this->assertSame($expected['type'], $inboundSMS->getType());
        $this->assertSame($expected['keyword'], $inboundSMS->getKeyword());
        $this->assertSame($expected['message-timestamp'], $inboundSMS->getMessageTimestamp()->format('Y-m-d H:i:s'));
        $this->assertSame((int) $expected['timestamp'], $inboundSMS->getTimestamp());
        $this->assertSame($expected['nonce'], $inboundSMS->getNonce());
        $this->assertSame($expected['sig'], $inboundSMS->getSignature());
    }

    public function testCanCreateIncomingBinaryMessage()
    {
        $expected = $this->getQueryStringFromRequest('inbound-binary');

        $request = $this->getServerRequest('inbound-binary', 'GET');
        $inboundSMS = Factory::createFromRequest($request);

        $this->assertSame($expected['msisdn'], $inboundSMS->getMsisdn());
        $this->assertSame($expected['msisdn'], $inboundSMS->getFrom());
        $this->assertSame($expected['to'], $inboundSMS->getTo());
        $this->assertSame($expected['messageId'], $inboundSMS->getMessageId());
        $this->assertSame($expected['text'], $inboundSMS->getText());
        $this->assertSame($expected['type'], $inboundSMS->getType());
        $this->assertSame($expected['keyword'], $inboundSMS->getKeyword());
        $this->assertSame($expected['data'], $inboundSMS->getData());
        $this->assertSame($expected['udh'], $inboundSMS->getUdh());
    }

    public function testCanCreateFromConcatMessageFormPostServerRequest()
    {
        $expected = $this->getQueryStringFromRequest('inbound-long');

        $request = $this->getServerRequest('inbound-long');
        $inboundSMS = Factory::createFromRequest($request);

        $this->assertSame($expected['msisdn'], $inboundSMS->getMsisdn());
        $this->assertSame($expected['msisdn'], $inboundSMS->getFrom());
        $this->assertSame($expected['to'], $inboundSMS->getTo());
        $this->assertSame($expected['messageId'], $inboundSMS->getMessageId());
        $this->assertSame($expected['text'], $inboundSMS->getText());
        $this->assertSame($expected['type'], $inboundSMS->getType());
        $this->assertSame($expected['keyword'], $inboundSMS->getKeyword());
        $this->assertSame($expected['message-timestamp'], $inboundSMS->getMessageTimestamp()->format('Y-m-d H:i:s'));
        $this->assertSame((bool) $expected['concat'], $inboundSMS->getConcat());
        $this->assertSame((int) $expected['concat-part'], $inboundSMS->getConcatPart());
        $this->assertSame($expected['concat-ref'], $inboundSMS->getConcatRef());
        $this->assertSame((int) $expected['concat-total'], $inboundSMS->getConcatTotal());
    }

    public function testThrowRuntimeExceptionWhenInvalidRequestDetected()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Invalid method for incoming webhook");

        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest('DELETE', '/');

        Factory::createFromRequest($request);
    }

    public function testCanCreateFromJSONPostServerRequest()
    {
        $expected = $this->getBodyFromRequest('json');

        $request = $this->getServerRequest('json');
        $inboundSMS = Factory::createFromRequest($request);

        $this->assertSame($expected['msisdn'], $inboundSMS->getMsisdn());
        $this->assertSame($expected['to'], $inboundSMS->getTo());
        $this->assertSame($expected['messageId'], $inboundSMS->getMessageId());
        $this->assertSame($expected['text'], $inboundSMS->getText());
        $this->assertSame($expected['type'], $inboundSMS->getType());
        $this->assertSame($expected['keyword'], $inboundSMS->getKeyword());
        $this->assertSame($expected['message-timestamp'], $inboundSMS->getMessageTimestamp()->format('Y-m-d H:i:s'));
        $this->assertSame((int) $expected['timestamp'], $inboundSMS->getTimestamp());
        $this->assertSame($expected['nonce'], $inboundSMS->getNonce());
        $this->assertSame($expected['sig'], $inboundSMS->getSignature());
    }

    public function testCanCreateFromRawArray()
    {
        $expected = $this->getQueryStringFromRequest('inbound');

        $inboundSMS = new InboundSMS($expected);

        $this->assertSame($expected['msisdn'], $inboundSMS->getMsisdn());
        $this->assertSame($expected['msisdn'], $inboundSMS->getFrom());
        $this->assertSame($expected['to'], $inboundSMS->getTo());
        $this->assertSame($expected['messageId'], $inboundSMS->getMessageId());
        $this->assertSame($expected['text'], $inboundSMS->getText());
        $this->assertSame($expected['type'], $inboundSMS->getType());
        $this->assertSame($expected['keyword'], $inboundSMS->getKeyword());
        $this->assertSame($expected['message-timestamp'], $inboundSMS->getMessageTimestamp()->format('Y-m-d H:i:s'));
        $this->assertSame((int) $expected['timestamp'], $inboundSMS->getTimestamp());
        $this->assertSame($expected['nonce'], $inboundSMS->getNonce());
        $this->assertSame($expected['sig'], $inboundSMS->getSignature());
    }

    public function testCanCreateFromGetWithBodyServerRequest()
    {
        $expected = $this->getQueryStringFromRequest('inbound');

        $request = $this->getServerRequest('inbound');
        $inboundSMS = Factory::createFromRequest($request);

        $this->assertSame($expected['msisdn'], $inboundSMS->getMsisdn());
        $this->assertSame($expected['to'], $inboundSMS->getTo());
        $this->assertSame($expected['messageId'], $inboundSMS->getMessageId());
        $this->assertSame($expected['text'], $inboundSMS->getText());
        $this->assertSame($expected['type'], $inboundSMS->getType());
        $this->assertSame($expected['keyword'], $inboundSMS->getKeyword());
        $this->assertSame($expected['message-timestamp'], $inboundSMS->getMessageTimestamp()->format('Y-m-d H:i:s'));
        $this->assertSame((int) $expected['timestamp'], $inboundSMS->getTimestamp());
        $this->assertSame($expected['nonce'], $inboundSMS->getNonce());
        $this->assertSame($expected['sig'], $inboundSMS->getSignature());
    }

    public function testThrowsExceptionWithInvalidRequest()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Incoming SMS missing required data `msisdn`');

        $request = $this->getServerRequest('invalid')->getQueryParams();
        new InboundSMS($request);
    }

    protected function getQueryStringFromRequest(string $requestName)
    {
        $text = file_get_contents(__DIR__ . '/../requests/' . $requestName . '.txt');
        $request = Serializer::fromString($text);
        parse_str($request->getUri()->getQuery(), $query);

        return $query;
    }

    protected function getBodyFromRequest(string $requestName)
    {
        $text = file_get_contents(__DIR__ . '/../requests/' . $requestName . '.txt');
        $request = Serializer::fromString($text);
        return json_decode($request->getBody()->getContents(), true);
    }

    protected function getServerRequest(string $requestName)
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
