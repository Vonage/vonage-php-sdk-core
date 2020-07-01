<?php
declare(strict_types=1);

namespace NexmoTest\SMS;

use InvalidArgumentException;
use Nexmo\SMS\InboundSMS;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;

class InboundSMSTest extends TestCase
{
    public function testCanCreateFromFormPostServerRequest()
    {
        parse_str(file_get_contents(__DIR__ . '/requests/inbound.txt'), $expected);

        $request = $this->getServerRequest('inbound', 'POST');
        $inboundSMS = InboundSMS::createFromRequest($request);

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
        parse_str(file_get_contents(__DIR__ . '/requests/inbound-binary.txt'), $expected);

        $request = $this->getServerRequest('inbound-binary', 'POST');
        $inboundSMS = InboundSMS::createFromRequest($request);

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
        parse_str(file_get_contents(__DIR__ . '/requests/inbound-long.txt'), $expected);

        $request = $this->getServerRequest('inbound-long', 'POST');
        $inboundSMS = InboundSMS::createFromRequest($request);

        $this->assertSame($expected['api-key'], $inboundSMS->getApiKey());
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
        $this->expectExceptionMessage("Invalid request method for incoming SMS");

        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest('DELETE', '/');

        InboundSMS::createFromRequest($request);
    }

    public function testCanCreateFromJSONPostServerRequest()
    {
        $expected = json_decode(file_get_contents(__DIR__ . '/requests/json.txt'), true);

        $request = $this->getServerRequest('json', 'POST', ['Content-Type' => 'application/json']);
        $inboundSMS = InboundSMS::createFromRequest($request);

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
        parse_str(file_get_contents(__DIR__ . '/requests/inbound.txt'), $expected);

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
        parse_str(file_get_contents(__DIR__ . '/requests/inbound.txt'), $expected);

        $request = $this->getServerRequest();
        $inboundSMS = InboundSMS::createFromRequest($request);

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

        $request = $this->getServerRequest('invalid');
        InboundSMS::createFromRequest($request);
    }

    protected function getServerRequest(
        string $type = 'inbound',
        string $method = 'GET',
        array $headers = [],
        string $url = 'https://ohyt2ctr9l0z.runscope.net/sms_post'
    ) {
        $data = file_get_contents(__DIR__ . '/requests/' . $type . '.txt');
        $params = [];
        parse_str($data, $params);

        $query = [];
        $parsed = null;

        switch (strtoupper($method)) {
            case 'GET':
                $query = $params;
                $body = 'php://memory';
                break;
            default:
                $body = fopen(__DIR__ . '/requests/' . $type . '.txt', 'r');
                $query = [];
                $parsed = $params;
                if (isset($headers['Content-Type']) && $headers['Content-Type'] === 'application/json') {
                    $parsed = null;
                }
                break;
        }

        return new ServerRequest([], [], $url, $method, $body, $headers, [], $query, $parsed);
    }
}
