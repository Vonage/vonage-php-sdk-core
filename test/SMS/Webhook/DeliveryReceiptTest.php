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
use Vonage\SMS\Webhook\DeliveryReceipt;
use Vonage\SMS\Webhook\Factory;

use function file_get_contents;
use function json_decode;
use function parse_str;

class DeliveryReceiptTest extends TestCase
{
    public function testCanCreateFromGetServerRequest(): void
    {
        $expected = $this->getQueryStringFromRequest('dlr-get');
        $request = $this->getServerRequest('dlr-get');
        $dlr = Factory::createFromRequest($request);

        $this->assertSame($expected['msisdn'], $dlr->getMsisdn());
        $this->assertSame((int)$expected['err-code'], $dlr->getErrCode());
        $this->assertSame($expected['messageId'], $dlr->getMessageId());
        $this->assertSame($expected['network-code'], $dlr->getNetworkCode());
        $this->assertSame($expected['price'], $dlr->getPrice());
        $this->assertSame($expected['scts'], $dlr->getScts());
        $this->assertSame($expected['status'], $dlr->getStatus());
        $this->assertSame($expected['to'], $dlr->getTo());
        $this->assertSame($expected['api-key'], $dlr->getApiKey());
        $this->assertSame($expected['message-timestamp'], $dlr->getMessageTimestamp()->format('Y-m-d H:i:s'));
    }

    public function testCanCreateFromJSONPostServerRequest(): void
    {
        $expected = $this->getBodyFromRequest('dlr-post-json');
        $request = $this->getServerRequest('dlr-post-json');
        $dlr = Factory::createFromRequest($request);

        $this->assertSame($expected['msisdn'], $dlr->getMsisdn());
        $this->assertSame((int)$expected['err-code'], $dlr->getErrCode());
        $this->assertSame($expected['messageId'], $dlr->getMessageId());
        $this->assertSame($expected['network-code'], $dlr->getNetworkCode());
        $this->assertSame($expected['price'], $dlr->getPrice());
        $this->assertSame($expected['scts'], $dlr->getScts());
        $this->assertSame($expected['status'], $dlr->getStatus());
        $this->assertSame($expected['to'], $dlr->getTo());
        $this->assertSame($expected['api-key'], $dlr->getApiKey());
        $this->assertSame($expected['message-timestamp'], $dlr->getMessageTimestamp()->format('Y-m-d H:i:s'));
    }

    public function testCanCreateFromFormPostServerRequest(): void
    {
        $expected = $this->getBodyFromRequest('dlr-post', false);
        $request = $this->getServerRequest('dlr-post');
        $dlr = Factory::createFromRequest($request);

        $this->assertSame($expected['msisdn'], $dlr->getMsisdn());
        $this->assertSame((int)$expected['err-code'], $dlr->getErrCode());
        $this->assertSame($expected['messageId'], $dlr->getMessageId());
        $this->assertSame($expected['network-code'], $dlr->getNetworkCode());
        $this->assertSame($expected['price'], $dlr->getPrice());
        $this->assertSame($expected['scts'], $dlr->getScts());
        $this->assertSame($expected['status'], $dlr->getStatus());
        $this->assertSame($expected['to'], $dlr->getTo());
        $this->assertSame($expected['api-key'], $dlr->getApiKey());
        $this->assertSame($expected['message-timestamp'], $dlr->getMessageTimestamp()->format('Y-m-d H:i:s'));
    }

    /**
     * @throws Exception
     */
    public function testCanCreateFromRawArray(): void
    {
        $expected = $this->getQueryStringFromRequest('dlr-get');
        $dlr = new DeliveryReceipt($expected);

        $this->assertSame($expected['msisdn'], $dlr->getMsisdn());
        $this->assertSame((int)$expected['err-code'], $dlr->getErrCode());
        $this->assertSame($expected['messageId'], $dlr->getMessageId());
        $this->assertSame($expected['network-code'], $dlr->getNetworkCode());
        $this->assertSame($expected['price'], $dlr->getPrice());
        $this->assertSame($expected['scts'], $dlr->getScts());
        $this->assertSame($expected['status'], $dlr->getStatus());
        $this->assertSame($expected['to'], $dlr->getTo());
        $this->assertSame($expected['api-key'], $dlr->getApiKey());
        $this->assertSame($expected['message-timestamp'], $dlr->getMessageTimestamp()->format('Y-m-d H:i:s'));
    }

    /**
     * @throws Exception
     */
    public function testThrowsExceptionWithInvalidRequest(): void
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

    /**
     * @param bool $json
     */
    protected function getBodyFromRequest(string $requestName, $json = true)
    {
        $text = file_get_contents(__DIR__ . '/../requests/' . $requestName . '.txt');
        $request = Serializer::fromString($text);

        if ($json) {
            return json_decode($request->getBody()->getContents(), true);
        }

        parse_str($request->getBody()->getContents(), $params);

        return $params;
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
