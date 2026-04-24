<?php

declare(strict_types=1);

namespace VonageTest\Insights;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\BasicHandler;
use Vonage\Client\Exception\Request;
use Vonage\Insights\Advanced;
use Vonage\Insights\Client as InsightClient;
use VonageTest\Traits\HTTPTestTrait;
use VonageTest\VonageTestCase;

class AdvancedTest extends VonageTestCase
{
    use HTTPTestTrait;

    public InsightClient $insightClient;
    public Client|ObjectProphecy $vonageClient;
    public ObjectProphecy $httpClient;
    public APIResource $api;

    public function setUp(): void
    {
        $this->responsesDirectory = __DIR__ . '/responses';

        $this->vonageClient = $this->prophesize(Client::class);
        $this->httpClient = $this->prophesize(\Psr\Http\Client\ClientInterface::class);
        $this->vonageClient->getHttpClient()->willReturn($this->httpClient->reveal());
        $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');
        $this->vonageClient->getCredentials()->willReturn(
            new Client\Credentials\Container(
                new Client\Credentials\Basic('abc', 'def'),
            )
        );

        $this->api = (new APIResource($this->vonageClient->reveal()))
            ->setIsHAL(false)
            ->setAuthHandlers(new BasicHandler());

        $this->insightClient = new InsightClient($this->api);
    }
    /**
     * @dataProvider advancedTestProvider
     *
     * @param $advanced
     * @param $inputData
     */
    public function testObjectAccess($advanced, $inputData): void
    {
        $this->assertEquals($inputData['valid_number'], $advanced->getValidNumber());
        $this->assertEquals($inputData['reachable'], $advanced->getReachable());
    }

    /**
     * @dataProvider advancedExceptionResponseProvider
     * @param $responseName
     * @param $expectException
     *
     * @return void
     */
    public function testExceptionWhenNotChargeable($responseName, $expectException): void
    {
        if ($expectException) {
            $this->expectException(Request::class);
        }

        $this->httpClient->sendRequest(Argument::that(function (\Laminas\Diactoros\Request $request) use ($responseName) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals('https://api.nexmo.com/ni/advanced/json?number=12345', $uriString);
            return true;
        }))->willReturn($this->getResponse($responseName, 200));

        $response = $this->insightClient->advanced('12345');
        $this->assertInstanceOf(Advanced::class,  $response);
    }

    public function advancedExceptionResponseProvider(): array
    {
        return [
            ['advanced', false],
            ['advanced3', true],
            ['advanced4', true],
            ['advanced43', false],
            ['advanced44', false],
            ['advanced45', false]
        ];
    }

    public function advancedTestProvider(): array
    {
        $r = [];

        $input1 = [
            'valid_number' => 'valid',
            'reachable' => 'unknown'
        ];

        $advanced1 = new Advanced('01234567890');
        $advanced1->fromArray($input1);
        $r['standard-1'] = [$advanced1, $input1];

        return $r;
    }
}
