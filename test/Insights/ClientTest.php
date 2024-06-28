<?php

declare(strict_types=1);

namespace VonageTest\Insights;

use Laminas\Diactoros\Response;
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use VonageTest\Traits\Psr7AssertionTrait;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Exception\Request as RequestException;
use Vonage\Client\Exception\Server as ServerException;
use Vonage\Insights\Advanced;
use Vonage\Insights\AdvancedCnam;
use Vonage\Insights\Basic;
use Vonage\Insights\Client as InsightsClient;
use Vonage\Insights\Standard;
use Vonage\Insights\StandardCnam;
use VonageTest\VonageTestCase;
use function fopen;

class ClientTest extends VonageTestCase
{
    use Psr7AssertionTrait;

    protected APIResource $apiClient;

    protected $vonageClient;

    protected APIResource $api;

    /**
     * @var InsightsClient
     */
    protected $insightsClient;

    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getApiUrl()->willReturn('http://api.nexmo.com');
        $this->vonageClient->getCredentials()->willReturn(
            new Client\Credentials\Container(
                new Client\Credentials\Basic('abc', 'def'),
            )
        );

        $this->api = new APIResource();
        $this->api->setIsHAL(false);
        $this->api->setClient($this->vonageClient->reveal());

        $this->insightsClient = new InsightsClient($this->api);
    }

    public function testStandardCnam(): void
    {
        $this->checkInsightsRequestCnam('standardCnam', '/ni/standard/json', StandardCnam::class);
    }

    public function testAdvancedCnam(): void
    {
        $this->checkInsightsRequestCnam('advancedCnam', '/ni/advanced/json', AdvancedCnam::class);
    }

    /**
     * @throws Client\Exception\Exception
     * @throws RequestException
     * @throws ServerException
     * @throws ClientExceptionInterface
     */
    public function testAdvancedAsync(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/ni/advanced/async/json', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
           $this->assertRequestMethod('GET', $request);
            $this->assertRequestQueryContains("number", "14155550100", $request);
            $this->assertRequestQueryContains("callback", "example.com/hook", $request);

            return true;
        }))->willReturn($this->getResponse('advancedAsync'));

        $this->insightsClient->advancedAsync('14155550100', 'example.com/hook');
    }

    public function testBasic(): void
    {
        $this->checkInsightsRequest('basic', '/ni/basic/json', Basic::class);
    }

    public function testStandard(): void
    {
        $this->checkInsightsRequest('standard', '/ni/standard/json', Standard::class);
    }

    public function testAdvanced(): void
    {
        $this->checkInsightsRequest('advanced', '/ni/advanced/json', Advanced::class);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws RequestException
     * @throws ServerException
     */
    public function testError(): void
    {
        $this->expectException(RequestException::class);

        $this->vonageClient->send(Argument::that(fn(RequestInterface $request) => true))->willReturn($this->getResponse('error'));

        $this->insightsClient->basic('14155550100');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws RequestException
     * @throws ServerException
     */
    public function testClientException(): void
    {
        $this->expectException(RequestException::class);

        $this->vonageClient->send(Argument::that(fn(RequestInterface $request) => true))->willReturn($this->getResponse('error', 401));

        $this->insightsClient->basic('14155550100');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws RequestException
     * @throws ServerException
     */
    public function testServerException(): void
    {
        $this->expectException(ServerException::class);

        $this->vonageClient->send(Argument::that(fn(RequestInterface $request) => true))->willReturn($this->getResponse('error', 502));

        $this->insightsClient->basic('14155550100');
    }

    protected function checkInsightsRequest($methodToCall, $expectedPath, $expectedClass): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($expectedPath) {
            $this->assertEquals($expectedPath, $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
           $this->assertRequestMethod('GET', $request);

            $this->assertRequestQueryContains("number", "14155550100", $request);
            return true;
        }))->willReturn($this->getResponse($methodToCall));

        $insightsStandard = @$this->insightsClient->$methodToCall('14155550100');
        $this->assertInstanceOf($expectedClass, $insightsStandard);
        $this->assertEquals('(415) 555-0100', $insightsStandard->getNationalFormatNumber());
    }

    protected function checkInsightsRequestCnam($methodToCall, $expectedPath, $expectedClass): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($expectedPath) {
            $this->assertEquals($expectedPath, $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
           $this->assertRequestMethod('GET', $request);

            $this->assertRequestQueryContains("number", "14155550100", $request);
            $this->assertRequestQueryContains("cnam", "true", $request);
            return true;
        }))->willReturn($this->getResponse($methodToCall));

        $insightsStandard = @$this->insightsClient->$methodToCall('14155550100');
        $this->assertInstanceOf($expectedClass, $insightsStandard);
        $this->assertEquals('(415) 555-0100', $insightsStandard->getNationalFormatNumber());
    }

    /**
     * Get the API response we'd expect for a call to the API.
     */
    protected function getResponse(string $type = 'success', int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
    }
}
