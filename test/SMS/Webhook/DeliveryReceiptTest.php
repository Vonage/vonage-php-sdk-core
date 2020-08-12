<?php
declare(strict_types=1);

namespace VonageTest\SMS\Webhook;

use RuntimeException;
use InvalidArgumentException;
use Vonage\SMS\Webhook\DeliveryReceipt;
use Vonage\SMS\Webhook\Factory;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Request\Serializer;
use Zend\Diactoros\ServerRequestFactory;

class DeliveryReceiptTest extends TestCase
{
    public function testCanCreateFromGetServerRequest()
    {
        $expected = $this->getQueryStringFromRequest('dlr-get');

        $request = $this->getServerRequest('dlr-get');
        $dlr = Factory::createFromRequest($request);

        $this->assertSame($expected['msisdn'], $dlr->getMsisdn());
        $this->assertSame((int) $expected['err-code'], $dlr->getErrCode());
        $this->assertSame($expected['messageId'], $dlr->getMessageId());
        $this->assertSame($expected['network-code'], $dlr->getNetworkCode());
        $this->assertSame($expected['price'], $dlr->getPrice());
        $this->assertSame($expected['scts'], $dlr->getScts());
        $this->assertSame($expected['status'], $dlr->getStatus());
        $this->assertSame($expected['to'], $dlr->getTo());
        $this->assertSame($expected['api-key'], $dlr->getApiKey());
        $this->assertSame($expected['message-timestamp'], $dlr->getMessageTimestamp()->format('Y-m-d H:i:s'));
    }

    public function testCanCreateFromJSONPostServerRequest()
    {
        $expected = $this->getBodyFromRequest('dlr-post-json');

        $request = $this->getServerRequest('dlr-post-json');
        $dlr = Factory::createFromRequest($request);

        $this->assertSame($expected['msisdn'], $dlr->getMsisdn());
        $this->assertSame((int) $expected['err-code'], $dlr->getErrCode());
        $this->assertSame($expected['messageId'], $dlr->getMessageId());
        $this->assertSame($expected['network-code'], $dlr->getNetworkCode());
        $this->assertSame($expected['price'], $dlr->getPrice());
        $this->assertSame($expected['scts'], $dlr->getScts());
        $this->assertSame($expected['status'], $dlr->getStatus());
        $this->assertSame($expected['to'], $dlr->getTo());
        $this->assertSame($expected['api-key'], $dlr->getApiKey());
        $this->assertSame($expected['message-timestamp'], $dlr->getMessageTimestamp()->format('Y-m-d H:i:s'));
    }

    public function testCanCreateFromFormPostServerRequest()
    {
        $expected = $this->getBodyFromRequest('dlr-post', false);

        $request = $this->getServerRequest('dlr-post');
        $dlr = Factory::createFromRequest($request);

        $this->assertSame($expected['msisdn'], $dlr->getMsisdn());
        $this->assertSame((int) $expected['err-code'], $dlr->getErrCode());
        $this->assertSame($expected['messageId'], $dlr->getMessageId());
        $this->assertSame($expected['network-code'], $dlr->getNetworkCode());
        $this->assertSame($expected['price'], $dlr->getPrice());
        $this->assertSame($expected['scts'], $dlr->getScts());
        $this->assertSame($expected['status'], $dlr->getStatus());
        $this->assertSame($expected['to'], $dlr->getTo());
        $this->assertSame($expected['api-key'], $dlr->getApiKey());
        $this->assertSame($expected['message-timestamp'], $dlr->getMessageTimestamp()->format('Y-m-d H:i:s'));
    }

    public function testCanCreateFromRawArray()
    {
        $expected = $this->getQueryStringFromRequest('dlr-get');
        $dlr = new DeliveryReceipt($expected);

        $this->assertSame($expected['msisdn'], $dlr->getMsisdn());
        $this->assertSame((int) $expected['err-code'], $dlr->getErrCode());
        $this->assertSame($expected['messageId'], $dlr->getMessageId());
        $this->assertSame($expected['network-code'], $dlr->getNetworkCode());
        $this->assertSame($expected['price'], $dlr->getPrice());
        $this->assertSame($expected['scts'], $dlr->getScts());
        $this->assertSame($expected['status'], $dlr->getStatus());
        $this->assertSame($expected['to'], $dlr->getTo());
        $this->assertSame($expected['api-key'], $dlr->getApiKey());
        $this->assertSame($expected['message-timestamp'], $dlr->getMessageTimestamp()->format('Y-m-d H:i:s'));
    }

    public function testThrowsExceptionWithInvalidRequest()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Delivery Receipt missing required data `err-code`');

        $request = $this->getServerRequest('invalid')->getQueryParams();
        new DeliveryReceipt($request);
    }

    protected function getQueryStringFromRequest(string $requestName)
    {
        $text = file_get_contents(__DIR__ . '/../requests/' . $requestName . '.txt');
        $request = Serializer::fromString($text);
        parse_str($request->getUri()->getQuery(), $query);

        return $query;
    }

    protected function getBodyFromRequest(string $requestName, $json = true)
    {
        $text = file_get_contents(__DIR__ . '/../requests/' . $requestName . '.txt');
        $request = Serializer::fromString($text);
        if ($json) {
            return json_decode($request->getBody()->getContents(), true);
        } else {
            parse_str($request->getBody()->getContents(), $params);
            return $params;
        }
        
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
