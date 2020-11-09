<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Conversion;

use Laminas\Diactoros\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Client\Exception\Request as RequestException;
use Vonage\Client\Exception\Server as ServerException;
use Vonage\Conversion\Client as ConversionClient;
use VonageTest\Psr7AssertionTrait;

use function fopen;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

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
        $this->vonageClient = $this->getMockBuilder('Vonage\Client')
            ->disableOriginalConstructor()
            ->setMethods(['send', 'getApiUrl'])
            ->getMock();
        $this->vonageClient->method('getApiUrl')->willReturn('https://api.nexmo.com');

        $this->apiResource = new APIResource();
        $this->apiResource
            ->setBaseUri('/conversions/')
            ->setClient($this->vonageClient);

        $this->conversionClient = new ConversionClient($this->apiResource);
        $this->conversionClient->setClient($this->vonageClient);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     */
    public function testSmsWithTimestamp(): void
    {
        $this->vonageClient->method('send')->willReturnCallback(function (RequestInterface $request) {
            $this->assertEquals('/conversions/sms', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestQueryContains('message-id', 'ABC123', $request);
            $this->assertRequestQueryContains('delivered', '1', $request);
            $this->assertRequestQueryContains('timestamp', '123456', $request);

            return $this->getResponse();
        });

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
        $this->vonageClient->method('send')->willReturnCallback(function (RequestInterface $request) {
            $this->assertEquals('/conversions/sms', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestQueryContains('message-id', 'ABC123', $request);
            $this->assertRequestQueryContains('delivered', '1', $request);
            $this->assertRequestQueryNotContains('timestamp', $request);

            return $this->getResponse();
        });

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
        $this->vonageClient->method('send')->willReturnCallback(function (RequestInterface $request) {
            $this->assertEquals('/conversions/voice', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestQueryContains('message-id', 'ABC123', $request);
            $this->assertRequestQueryContains('delivered', '1', $request);
            $this->assertRequestQueryContains('timestamp', '123456', $request);

            return $this->getResponse();
        });

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
        $this->vonageClient->method('send')->willReturnCallback(function (RequestInterface $request) {
            $this->assertEquals('/conversions/voice', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestQueryContains('message-id', 'ABC123', $request);
            $this->assertRequestQueryContains('delivered', '1', $request);
            $this->assertRequestQueryNotContains('timestamp', $request);

            return $this->getResponse();
        });

        $this->conversionClient->voice('ABC123', true);
    }

    /**
     * Get the API response we'd expect for a call to the API.
     */
    protected function getResponse(): Response
    {
        return new Response(fopen('data://text/plain,', 'rb'), 200);
    }
}
