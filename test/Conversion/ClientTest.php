<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Conversion;

use Prophecy\Argument;
use Vonage\Conversion\Client;
use Zend\Diactoros\Response;
use Vonage\Client\APIResource;
use PHPUnit\Framework\TestCase;
use VonageTest\Psr7AssertionTrait;
use Psr\Http\Message\RequestInterface;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    protected $vonageClient;

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
        $this->vonageClient = $this->getMockBuilder('Vonage\Client')->disableOriginalConstructor()->setMethods(['send', 'getApiUrl'])->getMock();
        $this->vonageClient->method('getApiUrl')->will($this->returnValue('https://api.nexmo.com'));

        $this->apiResource = new APIResource();
        $this->apiResource
            ->setBaseUri('/conversions/')
            ->setClient($this->vonageClient)
        ;

        $this->conversionClient = new Client($this->apiResource);
        $this->conversionClient->setClient($this->vonageClient);
    }

    public function testSmsWithTimestamp()
    {
        $this->vonageClient->method('send')->will($this->returnCallback(function (RequestInterface $request) {
            $this->assertEquals('/conversions/sms', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestQueryContains('message-id', 'ABC123', $request);
            $this->assertRequestQueryContains('delivered', '1', $request);
            $this->assertRequestQueryContains('timestamp', '123456', $request);
            return $this->getResponse();
        }));

        $this->conversionClient->sms('ABC123', true, '123456');
    }

    public function testSmsWithoutTimestamp()
    {
        $this->vonageClient->method('send')->will($this->returnCallback(function (RequestInterface $request) {
            $this->assertEquals('/conversions/sms', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestQueryContains('message-id', 'ABC123', $request);
            $this->assertRequestQueryContains('delivered', '1', $request);
            $this->assertRequestQueryNotContains('timestamp', $request);
            return $this->getResponse();
        }));

        $this->conversionClient->sms('ABC123', true);
    }

    public function testVoiceWithTimestamp()
    {
        $this->vonageClient->method('send')->will($this->returnCallback(function (RequestInterface $request) {
            $this->assertEquals('/conversions/voice', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestQueryContains('message-id', 'ABC123', $request);
            $this->assertRequestQueryContains('delivered', '1', $request);
            $this->assertRequestQueryContains('timestamp', '123456', $request);
            return $this->getResponse();
        }));

        $this->conversionClient->voice('ABC123', true, '123456');
    }

    public function testVoiceWithoutTimestamp()
    {
        $this->vonageClient->method('send')->will($this->returnCallback(function (RequestInterface $request) {
            $this->assertEquals('/conversions/voice', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestQueryContains('message-id', 'ABC123', $request);
            $this->assertRequestQueryContains('delivered', '1', $request);
            $this->assertRequestQueryNotContains('timestamp', $request);
            return $this->getResponse();
        }));

        $this->conversionClient->voice('ABC123', true);
    }

    /**
     * Get the API response we'd expect for a call to the API.
     *
     * @param string $type
     * @return Response
     */
    protected function getResponse($type = 'success', $status = 200)
    {
        return new Response(fopen('data://text/plain,','r'), $status);
    }
}
