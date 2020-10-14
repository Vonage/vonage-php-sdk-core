<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\Test\Message;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use Vonage\Client\Exception\Exception;
use Vonage\Message\InboundMessage;
use Vonage\Message\Message;

class InboundMessageTest extends TestCase
{
    public function testConstructionWithId(): void
    {
        $message = new InboundMessage('test1234');
        self::assertSame('test1234', $message->getMessageId());
    }

    /**
     * Inbound messages can be created from a PSR-7 server request.
     *
     * @dataProvider getRequests
     *
     * @param ServerRequest $request
     */
    public function testCanCreateWithServerRequest(ServerRequest $request): void
    {
        $message = @new InboundMessage($request);

        /** @var array $requestData */
        $requestData = @$message->getRequestData();

        $originalData = $request->getQueryParams();

        if ('POST' === $request->getMethod()) {
            $originalData = $request->getParsedBody();

            $contentTypeHeader = $request->getHeader('Content-Type');

            if (array_key_exists(0, $contentTypeHeader) && 'application/json' === $contentTypeHeader[0]) {
                $originalData = json_decode((string)$request->getBody(), true);
            }
        }

        self::assertCount(count($originalData), $requestData);

        foreach ($originalData as $key => $value) {
            self::assertSame($value, $requestData[$key]);
        }
    }

    public function testCanCheckValid(): void
    {
        $request = $this->getServerRequest();
        $message = @new InboundMessage($request);

        self::assertTrue($message->isValid());

        $request = $this->getServerRequest('http://example.com', 'GET', 'invalid');
        $message = @new InboundMessage($request);

        self::assertFalse($message->isValid());
    }

    /**
     * Can access expected params via getters.
     *
     * @dataProvider getRequests
     * @param $request
     */
    public function testRequestObjectAccess($request): void
    {
        $message = @new InboundMessage($request);

        self::assertEquals('14845552121', $message->getFrom());
        self::assertEquals('16105553939', $message->getTo());
        self::assertEquals('02000000DA7C52E7', $message->getMessageId());
        self::assertEquals('Test this.', $message->getBody());
        self::assertEquals('text', $message->getType());
    }

    /**
     * Can access raw params via array access.
     *
     * @dataProvider getRequests
     * @param $request
     */
    public function testRequestArrayAccess($request): void
    {
        $message = @new InboundMessage($request);

        self::assertEquals('14845552121', @$message['msisdn']);
        self::assertEquals('16105553939', @$message['to']);
        self::assertEquals('02000000DA7C52E7', @$message['messageId']);
        self::assertEquals('Test this.', @$message['text']);
        self::assertEquals('text', @$message['type']);
    }

    /**
     * Can access expected params when populated from an API request.
     *
     * @dataProvider getResponses
     * @param $response
     */
    public function testResponseObjectAccess($response): void
    {
        $message = new InboundMessage('02000000DA7C52E7');
        @$message->setResponse($response);

        self::assertEquals('14845552121', $message->getFrom());
        self::assertEquals('16105553939', $message->getTo());
        self::assertEquals('02000000DA7C52E7', $message->getMessageId());
        self::assertEquals('Test this.', $message->getBody());
        self::assertEquals('6cff3913', $message->getAccountId());
        self::assertEquals('US-VIRTUAL-BANDWIDTH', $message->getNetwork());
    }

    /**
     * Can access raw params when populated from an API request.
     *
     * @dataProvider getResponses
     * @param $response
     */
    public function testResponseArrayAccess($response): void
    {
        $message = new InboundMessage('02000000DA7C52E7');
        @$message->setResponse($response);

        self::assertEquals('14845552121', @$message['from']);
        self::assertEquals('16105553939', @$message['to']);
        self::assertEquals('02000000DA7C52E7', @$message['message-id']);
        self::assertEquals('Test this.', @$message['body']);
        self::assertEquals('MO', @$message['type']);
        self::assertEquals('6cff3913', @$message['account-id']);
        self::assertEquals('US-VIRTUAL-BANDWIDTH', @$message['network']);
    }

    /**
     * @throws Exception
     */
    public function testCanCreateReply(): void
    {
        $message = @new InboundMessage($this->getServerRequest());
        $reply = $message->createReply('this is a reply');

        self::assertInstanceOf(Message::class, $reply);

        $params = $reply->getRequestData(false);

        self::assertEquals('14845552121', $params['to']);
        self::assertEquals('16105553939', $params['from']);
        self::assertEquals('this is a reply', $params['text']);
    }

    /**
     * @return Response[]
     */
    public function getResponses(): array
    {
        return [
            [$this->getResponse('search-inbound')]
        ];
    }

    /**
     * @return ServerRequest[]
     */
    public function getRequests(): array
    {
        return [
            'post, application/json' => [
                $this->getServerRequest(
                    'https://ohyt2ctr9l0z.runscope.net/sms_post',
                    'POST',
                    'json',
                    ['Content-Type' => 'application/json']
                )
            ],
            'post, form-encoded' => [
                $this->getServerRequest(
                    'https://ohyt2ctr9l0z.runscope.net/sms_post',
                    'POST',
                    'inbound'
                )
            ],
            'get, form-encoded' => [
                $this->getServerRequest(
                    'https://ohyt2ctr9l0z.runscope.net/sms_post',
                    'GET',
                    'inbound'
                )
            ],
        ];
    }

    /**
     * @param string $url
     * @param string $method
     * @param string $type
     * @param array $headers
     * @return ServerRequest
     */
    protected function getServerRequest(
        $url = 'https://ohyt2ctr9l0z.runscope.net/sms_post',
        $method = 'GET',
        $type = 'inbound',
        $headers = []
    ): ServerRequest {
        $data = file_get_contents(__DIR__ . '/requests/' . $type . '.txt');
        $params = [];
        $parsed = null;

        parse_str($data, $params);

        if (strtoupper($method) === 'GET') {
            $query = $params;
            $body = 'php://memory';
        } else {
            $body = fopen(__DIR__ . '/requests/' . $type . '.txt', 'rb');
            $query = [];
            $parsed = $params;

            if (isset($headers['Content-Type']) && $headers['Content-Type'] === 'application/json') {
                $parsed = null;
            }
        }

        return new ServerRequest([], [], $url, $method, $body, $headers, [], $query, $parsed);
    }

    /**
     * Get the API response we'd expect for a call to the API.
     *
     * @param string $type
     * @return Response
     */
    protected function getResponse(string $type = 'success'): Response
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'));
    }
}
