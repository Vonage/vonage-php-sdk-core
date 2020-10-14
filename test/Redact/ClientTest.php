<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\Test\Redact;

use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Exception;
use Vonage\Redact\Client as RedactClient;
use Vonage\Test\Psr7AssertionTrait;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    /**
     * @var APIResource
     */
    protected $apiClient;

    /**
     * @var mixed
     */
    protected $vonageClient;

    /**
     * @var RedactClient
     */
    protected $redact;

    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');

        $this->redact = new RedactClient();
        /** @noinspection PhpParamsInspection */
        $this->redact->setClient($this->vonageClient->reveal());
    }

    /**
     * @throws Exception\Exception
     * @throws ClientExceptionInterface
     */
    public function testUrlAndMethod(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            self::assertEquals('/v1/redact/transaction', $request->getUri()->getPath());
            self::assertEquals('api.nexmo.com', $request->getUri()->getHost());
            self::assertEquals('POST', $request->getMethod());

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('success', 204));

        $this->redact->transaction('ABC123', 'sms');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     */
    public function testNoOptions(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            self::assertRequestJsonBodyContains('id', 'ABC123', $request);
            self::assertRequestJsonBodyContains('product', 'sms', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('success', 204));

        $this->redact->transaction('ABC123', 'sms');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     */
    public function testWithOptions(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            self::assertRequestJsonBodyContains('id', 'ABC123', $request);
            self::assertRequestJsonBodyContains('product', 'sms', $request);
            self::assertRequestJsonBodyContains('type', 'inbound', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('success', 204));

        $this->redact->transaction('ABC123', 'sms', ['type' => 'inbound']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     */
    public function testOptionsDoNotOverwriteParams(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            self::assertRequestJsonBodyContains('id', 'ABC123', $request);
            self::assertRequestJsonBodyContains('product', 'sms', $request);
            self::assertRequestJsonBodyContains('type', 'inbound', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('success', 204));

        $this->redact->transaction('ABC123', 'sms', ['id' => 'ZZZ', 'type' => 'inbound']);
    }

    /**
     * @dataProvider exceptionsProvider
     * @param $response
     * @param $code
     * @param $expectedException
     * @param $expectedMessage
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     */
    public function testExceptions($response, $code, $expectedException, $expectedMessage): void
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessage);

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            return $request instanceof RequestInterface;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse($response, $code));

        $this->redact->transaction('ABC123', 'sms');
    }

    /**
     * @return array[]
     */
    public function exceptionsProvider(): array
    {
        return [
            'unauthorized' => ['unauthorized', 401, Exception\Request::class, "Unauthorized"],
            'premature-redaction' => [
                'premature-redaction',
                403,
                Exception\Request::class,
                "Premature Redaction - You must wait 60 minutes before redacting ID '0A000000B0C9A1234'. " .
                "See https://developer.nexmo.com/api-errors/redact#premature-redaction"
            ],
            'unprovisioned' => [
                'unprovisioned',
                403,
                Exception\Request::class,
                "Authorisation error - User=ABC123 is not provisioned to redact product=SMS. " .
                "See https://developer.nexmo.com/api-errors#unprovisioned"
            ],
            'invalid-id' => [
                'invalid-id',
                404,
                Exception\Request::class,
                "Invalid ID - ID '0A000000B0C9A1234' could not be found (type=MT). " .
                "See https://developer.nexmo.com/api-errors#invalid-id"
            ],
            'invalid-json' => [
                'invalid-json',
                422,
                Exception\Request::class,
                "Invalid JSON - Unexpected character ('\"' (code 34)): was expecting comma to separate " .
                "Object entries. See https://developer.nexmo.com/api-errors#invalid-json"
            ],
            'unsupported-product' => [
                'unsupported-product',
                422,
                Exception\Request::class,
                "Invalid Product - No product corresponding to supplied string sms2!. " .
                "See https://developer.nexmo.com/api-errors/redact#invalid-product"
            ],
            'unknown-error' => [
                'error',
                500,
                Exception\Server::class,
                "Unexpected error"
            ],
        ];
    }

    /**
     * Get the API response we'd expect for a call to the API.
     *
     * @param string $type
     * @param int $status
     * @return Response
     */
    protected function getResponse(string $type = 'success', int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
    }
}
