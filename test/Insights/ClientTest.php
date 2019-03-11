<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Insights;

use Nexmo\Client\Exception;
use Nexmo\Insights\AdvancedCnam;
use Nexmo\Insights\Basic;
use Nexmo\Insights\Standard;
use Nexmo\Insights\Advanced;
use Nexmo\Insights\Client;
use Nexmo\Insights\StandardCnam;
use NexmoTest\Psr7AssertionTrait;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Response;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    protected $nexmoClient;

    /**
     * @var Client
     */
    protected $insightsClient;

    public function setUp()
    {
        $this->nexmoClient = $this->prophesize('Nexmo\Client');
        $this->nexmoClient->getApiUrl()->willReturn('http://api.nexmo.com');
        $this->insightsClient = new Client();
        $this->insightsClient->setClient($this->nexmoClient->reveal());
    }

    public function testStandardCnam()
    {
        $this->checkInsightsRequestCnam('standardCnam', '/ni/standard/json', StandardCnam::class);
    }

    public function testAdvancedCnam()
    {
        $this->checkInsightsRequestCnam('advancedCnam', '/ni/advanced/json', AdvancedCnam::class);
    }

    public function testAdvancedAsync()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/ni/advanced/async/json', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());

            $this->assertRequestQueryContains("number", "14155550100", $request);
            $this->assertRequestQueryContains("callback", "example.com/hook", $request);
            return true;
        }))->willReturn($this->getResponse('advancedAsync'));

        $this->insightsClient->advancedAsync('14155550100', 'example.com/hook');
    }

    public function testBasic()
    {
        $this->checkInsightsRequest('basic', '/ni/basic/json', Basic::class);
    }

    public function testStandard()
    {
        $this->checkInsightsRequest('standard', '/ni/standard/json', Standard::class);
    }

    public function testAdvanced()
    {
        $this->checkInsightsRequest('advanced', '/ni/advanced/json', Advanced::class);
    }

    public function errorProvider()
    {
        // yield 'testcasename' => ['methodToCall', 'expectedPath', 'expectedException', 'expectedMsg', 'expectedStatus'];
        yield 'basicRequest' => ['basic', '/ni/basic/json', Exception\Request::class, 'basic_request_error', 400];
        yield 'basicServer' => ['basic', '/ni/basic/json', Exception\Server::class, 'basic_server_error', 500];
        yield 'basicUnexpected' => ['basic', '/ni/basic/json', Exception\Exception::class, 'basic_unexpected_error', 399];
        yield 'standardRequest' => ['standard', '/ni/standard/json', Exception\Request::class, 'standard_request_error', 400];
        yield 'standardServer' => ['standard', '/ni/standard/json', Exception\Server::class, 'standard_server_error', 500];
        yield 'standardUnexpected' => ['standard', '/ni/standard/json', Exception\Exception::class, 'standard_unexpected_error', 399];
        yield 'advancedRequest' => ['advanced', '/ni/advanced/json', Exception\Request::class, 'advanced_request_error', 400];
        yield 'advancedServer' => ['advanced', '/ni/advanced/json', Exception\Server::class, 'advanced_server_error', 500];
        yield 'advancedUnexpected' => ['advanced', '/ni/advanced/json', Exception\Exception::class, 'advanced_unexpected_error', 399];
    }

    /**
     * @dataProvider errorProvider
     */
    public function testInsightsRequestError($methodToCall, $expectedPath, $expectedException, $expectedMessage, $expectedStatus)
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request)  use ($expectedPath){
            $this->assertEquals($expectedPath, $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());

            $this->assertRequestQueryContains("number", "14155550100", $request);
            return true;
        }))->willReturn($this->getErrorResponse($expectedMessage, $expectedStatus));

        $this->expectException($expectedException);
        if ($expectedStatus > 399 and $expectedStatus < 600) {
            $this->expectExceptionMessage($expectedMessage);
            $this->expectExceptionCode($expectedStatus);
        } else {
            $this->expectExceptionMessage('Unexpected HTTP Status Code');
            $this->expectExceptionCode(0);
        }
        $insightsStandard = $this->insightsClient->$methodToCall('14155550100');
    }

    public function testUnexpectedErrorBody()
    {
        $empty_json_payload = '{}';
        $http_status = 400;
        $expected_exception_type = Exception\Request::class;
        $expected_exception_msg = 'Unknown error';
        $this->nexmoClient->send(Argument::type(RequestInterface::class))
            // return a Response w/ http status 400, but no error-code-label element
            ->willReturn(new Response(fopen('data://text/plain,' . $empty_json_payload, 'r'), $http_status));

        $this->expectException($expected_exception_type);
        $this->expectExceptionMessage($expected_exception_msg);
        $this->expectExceptionCode($http_status);

        $this->insightsClient->basic('');
    }

    protected function checkInsightsRequest($methodToCall, $expectedPath, $expectedClass)
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request)  use ($expectedPath){
            $this->assertEquals($expectedPath, $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());

            $this->assertRequestQueryContains("number", "14155550100", $request);
            return true;
        }))->willReturn($this->getResponse($methodToCall));

        $insightsStandard = $this->insightsClient->$methodToCall('14155550100');
        $this->assertInstanceOf($expectedClass, $insightsStandard);
        $this->assertEquals('(415) 555-0100', $insightsStandard->getNationalFormatNumber());
    }

    protected function checkInsightsRequestCnam($methodToCall, $expectedPath, $expectedClass)
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request)  use ($expectedPath){
            $this->assertEquals($expectedPath, $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());

            $this->assertRequestQueryContains("number", "14155550100", $request);
            $this->assertRequestQueryContains("cnam", "true", $request);
            return true;
        }))->willReturn($this->getResponse($methodToCall));

        $insightsStandard = $this->insightsClient->$methodToCall('14155550100');
        $this->assertInstanceOf($expectedClass, $insightsStandard);
        $this->assertEquals('(415) 555-0100', $insightsStandard->getNationalFormatNumber());
    }

    /**
     * Get the API response we'd expect for a call to the API.
     *
     * @param string $type
     * @return Response
     */
    protected function getResponse($type = 'success', $code = 200)
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'r'), $code);
    }

    protected function getErrorResponse($error_code = 'test error', $status = 400)
    {
        $datstream = fopen('data://text/plain,{"error-code-label":"'. $error_code . '"}', 'r');
        $response = new Response($datstream, $status);

        return $response;
    }
}
