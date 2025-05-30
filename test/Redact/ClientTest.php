<?php

declare(strict_types=1);

namespace VonageTest\Redact;

use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Exception as ClientException;
use Vonage\Redact\Client as RedactClient;
use VonageTest\Traits\HTTPTestTrait;
use VonageTest\Traits\Psr7AssertionTrait;
use VonageTest\VonageTestCase;

class ClientTest extends VonageTestCase
{
    use Psr7AssertionTrait;
    use HTTPTestTrait;

    /**
     * @var APIResource
     */
    protected $apiClient;

    protected $vonageClient;

    /**
     * @var RedactClient
     */
    protected $redact;

    public function setUp(): void
    {
        $this->responsesDirectory = __DIR__ . '/responses';

        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');
        $this->vonageClient->getCredentials()->willReturn(
            new Client\Credentials\Container(new Client\Credentials\Basic('abc', 'def'))
        );

        $this->redact = new RedactClient();
        /** @noinspection PhpParamsInspection */
        $this->redact->setClient($this->vonageClient->reveal());
    }

    /**
     * @throws ClientException\Exception
     * @throws ClientExceptionInterface
     */
    public function testUrlAndMethod(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/v1/redact/transaction', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('success', 204));

        $this->redact->transaction('ABC123', 'sms');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     */
    public function testNoOptions(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestJsonBodyContains('id', 'ABC123', $request);
            $this->assertRequestJsonBodyContains('product', 'sms', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('success', 204));

        $this->redact->transaction('ABC123', 'sms');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     */
    public function testWithOptions(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestJsonBodyContains('id', 'ABC123', $request);
            $this->assertRequestJsonBodyContains('product', 'sms', $request);
            $this->assertRequestJsonBodyContains('type', 'inbound', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('success', 204));

        $this->redact->transaction('ABC123', 'sms', ['type' => 'inbound']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     */
    public function testOptionsDoNotOverwriteParams(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestJsonBodyContains('id', 'ABC123', $request);
            $this->assertRequestJsonBodyContains('product', 'sms', $request);
            $this->assertRequestJsonBodyContains('type', 'inbound', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('success', 204));

        $this->redact->transaction('ABC123', 'sms', ['id' => 'ZZZ', 'type' => 'inbound']);
    }

    /**
     * @dataProvider exceptionsProvider
     *
     * @param $response
     * @param $code
     * @param $expectedException
     * @param $expectedMessage
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     */
    public function testExceptions($response, $code, $expectedException, $expectedMessage): void
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessage);

        $this->vonageClient->send(Argument::that(fn (RequestInterface $request) => true))->shouldBeCalledTimes(1)->willReturn($this->getResponse($response, $code));

        $this->redact->transaction('ABC123', 'sms');
    }

    /**
     * @return array[]
     */
    public function exceptionsProvider(): array
    {
        return [
            'unauthorized' => ['unauthorized', 401, ClientException\Request::class, "Unauthorized"],
            'premature-redaction' => [
                'premature-redaction',
                403,
                ClientException\Request::class,
                "Premature Redaction - You must wait 60 minutes before redacting ID '0A000000B0C9A1234'. " .
                "See https://developer.nexmo.com/api-errors/redact#premature-redaction"
            ],
            'unprovisioned' => [
                'unprovisioned',
                403,
                ClientException\Request::class,
                "Authorisation error - User=ABC123 is not provisioned to redact product=SMS. " .
                "See https://developer.nexmo.com/api-errors#unprovisioned"
            ],
            'invalid-id' => [
                'invalid-id',
                404,
                ClientException\Request::class,
                "Invalid ID - ID '0A000000B0C9A1234' could not be found (type=MT). " .
                "See https://developer.nexmo.com/api-errors#invalid-id"
            ],
            'invalid-json' => [
                'invalid-json',
                422,
                ClientException\Request::class,
                "Invalid JSON - Unexpected character ('\"' (code 34)): was expecting comma to separate " .
                "Object entries. See https://developer.nexmo.com/api-errors#invalid-json"
            ],
            'unsupported-product' => [
                'unsupported-product',
                422,
                ClientException\Request::class,
                "Invalid Product - No product corresponding to supplied string sms2!. " .
                "See https://developer.nexmo.com/api-errors/redact#invalid-product"
            ],
            'unknown-error' => [
                'error',
                500,
                ClientException\Server::class,
                "Unexpected error"
            ],
        ];
    }
}
