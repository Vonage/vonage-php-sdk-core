<?php

declare(strict_types=1);

namespace VonageTest\Conversion;

use PHPUnit\Framework\MockObject\MockObject;
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Client;
use Vonage\Client as VonageClient;
use Vonage\Client\APIResource;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Client\Exception\Request as RequestException;
use Vonage\Client\Exception\Server as ServerException;
use Vonage\Conversion\Client as ConversionClient;
use VonageTest\Traits\HTTPTestTrait;
use VonageTest\Traits\Psr7AssertionTrait;
use VonageTest\VonageTestCase;

class ClientTest extends VonageTestCase
{
    use Psr7AssertionTrait;
    use HTTPTestTrait;

    /**
     * @var Client|MockObject
     */
    protected $vonageClient;

    private $conversionClient;

    /**
     * @var APIResource
     */
    protected $apiResource;

    /**
     * @var ConversionClient
     */
    protected $accountClient;

    public function setUp(): void
    {
        $this->responsesDirectory = 'data://text/plain,';

        $this->vonageClient = $this->prophesize(VonageClient::class);
        $this->vonageClient->getRestUrl()->willReturn('https://rest.nexmo.com');
        $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');

        $this->vonageClient->getCredentials()->willReturn(
            new Client\Credentials\Basic('abc', 'def'),
        );

        $this->apiResource = new APIResource();
        $this->apiResource
            ->setBaseUri('/conversions/')
            ->setAuthHandlers(new Client\Credentials\Handler\BasicHandler())
            ->setClient($this->vonageClient->reveal());

        $this->conversionClient = new ConversionClient($this->apiResource);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     */
    public function testSmsWithTimestamp(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestUrl('api.nexmo.com', '/conversions/sms', 'POST', $request);
            $this->assertRequestQueryContains('message-id', 'ABC123', $request);
            $this->assertRequestQueryContains('delivered', '1', $request);
            $this->assertRequestQueryContains('timestamp', '123456', $request);
            return true;
        }))->willReturn($this->getResponse());

        $this->conversionClient->sms('ABC123', true, '123456');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     */
    public function testSmsWithoutTimestamp(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestUrl('api.nexmo.com', '/conversions/sms', 'POST', $request);
            $this->assertRequestQueryContains('message-id', 'ABC123', $request);
            $this->assertRequestQueryContains('delivered', '1', $request);
            $this->assertRequestQueryNotContains('timestamp', $request);
            return true;
        }))->willReturn($this->getResponse());

        $this->conversionClient->sms('ABC123', true);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     */
    public function testVoiceWithTimestamp(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestUrl('api.nexmo.com', '/conversions/voice', 'POST', $request);
            $this->assertRequestQueryContains('message-id', 'ABC123', $request);
            $this->assertRequestQueryContains('delivered', '1', $request);
            $this->assertRequestQueryContains('timestamp', '123456', $request);
            return true;
        }))->willReturn($this->getResponse());

        $this->conversionClient->voice('ABC123', true, '123456');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     */
    public function testVoiceWithoutTimestamp(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestUrl('api.nexmo.com', '/conversions/voice', 'POST', $request);
            $this->assertRequestQueryContains('message-id', 'ABC123', $request);
            $this->assertRequestQueryContains('delivered', '1', $request);
            $this->assertRequestQueryNotContains('timestamp', $request);
            return true;
        }))->willReturn($this->getResponse());

        $this->conversionClient->voice('ABC123', true);
    }

    public function testExceptionHandler(): void
    {
        $this->expectException(Client\Exception\Request::class);

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            return true;
        }))->willReturn($this->getResponse('error', 402));

        $this->conversionClient->sms('ABC123', true, '123456');
    }
}
