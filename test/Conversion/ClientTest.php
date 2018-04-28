<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Conversion;

use Nexmo\Conversion\Client;
use Zend\Diactoros\Response;
use NexmoTest\Psr7AssertionTrait;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    protected $nexmoClient;

    /**
     * @var Client
     */
    protected $accountClient;

    public function setUp()
    {
        $this->nexmoClient = $this->getMockBuilder('Nexmo\Client')->disableOriginalConstructor()->setMethods(['send', 'getApiUrl'])->getMock();
        $this->nexmoClient->method('getApiUrl')->will($this->returnValue('https://api.nexmo.com'));
        $this->conversionClient = new Client();
        $this->conversionClient->setClient($this->nexmoClient);
    }

    public function testSmsWithTimestamp()
    {
        $this->nexmoClient->method('send')->will($this->returnCallback(function (RequestInterface $request) {
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
        $this->nexmoClient->method('send')->will($this->returnCallback(function (RequestInterface $request) {
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
        $this->nexmoClient->method('send')->will($this->returnCallback(function (RequestInterface $request) {
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
        $this->nexmoClient->method('send')->will($this->returnCallback(function (RequestInterface $request) {
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
