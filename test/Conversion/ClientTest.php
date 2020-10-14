<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\Test\Conversion;

use Laminas\Diactoros\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Exception\Exception;
use Vonage\Client\Exception\Request;
use Vonage\Client\Exception\Server;
use Vonage\Conversion\Client;
use Vonage\Test\Psr7AssertionTrait;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    /**
     * @var \Vonage\Client|MockObject
     */
    protected $vonageClient;

    private $conversionClient;

    /**
     * @var APIResource
     */
    protected $apiResource;

    /**
     * @var Client
     */
    protected $accountClient;

    public function setUp(): void
    {
        $this->vonageClient = $this->getMockBuilder('Vonage\Client')
            ->disableOriginalConstructor()
            ->setMethods(['send', 'getApiUrl'])
            ->getMock();
        $this->vonageClient->method('getApiUrl')->willReturn('https://api.nexmo.com');

        $this->apiResource = new APIResource();
        $this->apiResource
            ->setBaseUri('/conversions/')
            ->setClient($this->vonageClient);

        $this->conversionClient = new Client($this->apiResource);
        $this->conversionClient->setClient($this->vonageClient);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception
     * @throws Request
     * @throws Server
     */
    public function testSmsWithTimestamp(): void
    {
        $this->vonageClient->method('send')->willReturnCallback(function (RequestInterface $request) {
            self::assertEquals('/conversions/sms', $request->getUri()->getPath());
            self::assertEquals('api.nexmo.com', $request->getUri()->getHost());
            self::assertEquals('POST', $request->getMethod());
            self::assertRequestQueryContains('message-id', 'ABC123', $request);
            self::assertRequestQueryContains('delivered', '1', $request);
            self::assertRequestQueryContains('timestamp', '123456', $request);

            return $this->getResponse();
        });

        $this->conversionClient->sms('ABC123', true, '123456');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception
     * @throws Request
     * @throws Server
     */
    public function testSmsWithoutTimestamp(): void
    {
        $this->vonageClient->method('send')->willReturnCallback(function (RequestInterface $request) {
            self::assertEquals('/conversions/sms', $request->getUri()->getPath());
            self::assertEquals('api.nexmo.com', $request->getUri()->getHost());
            self::assertEquals('POST', $request->getMethod());
            self::assertRequestQueryContains('message-id', 'ABC123', $request);
            self::assertRequestQueryContains('delivered', '1', $request);
            self::assertRequestQueryNotContains('timestamp', $request);

            return $this->getResponse();
        });

        $this->conversionClient->sms('ABC123', true);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception
     * @throws Request
     * @throws Server
     */
    public function testVoiceWithTimestamp(): void
    {
        $this->vonageClient->method('send')->willReturnCallback(function (RequestInterface $request) {
            self::assertEquals('/conversions/voice', $request->getUri()->getPath());
            self::assertEquals('api.nexmo.com', $request->getUri()->getHost());
            self::assertEquals('POST', $request->getMethod());
            self::assertRequestQueryContains('message-id', 'ABC123', $request);
            self::assertRequestQueryContains('delivered', '1', $request);
            self::assertRequestQueryContains('timestamp', '123456', $request);

            return $this->getResponse();
        });

        $this->conversionClient->voice('ABC123', true, '123456');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception
     * @throws Request
     * @throws Server
     */
    public function testVoiceWithoutTimestamp(): void
    {
        $this->vonageClient->method('send')->willReturnCallback(function (RequestInterface $request) {
            self::assertEquals('/conversions/voice', $request->getUri()->getPath());
            self::assertEquals('api.nexmo.com', $request->getUri()->getHost());
            self::assertEquals('POST', $request->getMethod());
            self::assertRequestQueryContains('message-id', 'ABC123', $request);
            self::assertRequestQueryContains('delivered', '1', $request);
            self::assertRequestQueryNotContains('timestamp', $request);

            return $this->getResponse();
        });

        $this->conversionClient->voice('ABC123', true);
    }

    /**
     * Get the API response we'd expect for a call to the API.
     *
     * @return Response
     */
    protected function getResponse(): Response
    {
        return new Response(fopen('data://text/plain,', 'rb'), 200);
    }
}
