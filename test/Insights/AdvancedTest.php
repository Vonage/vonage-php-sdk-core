<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Insights;

use Laminas\Diactoros\Response;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\BasicQueryHandler;
use Vonage\Client\Exception\Request;
use VonageTest\VonageTestCase;
use Vonage\Insights\Advanced;
use Vonage\Insights\Client as InsightClient;

class AdvancedTest extends VonageTestCase
{
    public InsightClient $insightClient;
    public Client|ObjectProphecy $vonageClient;
    public APIResource $api;

    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getRestUrl()->willReturn('https://api.nexmo.com');
        $this->vonageClient->getCredentials()->willReturn(
            new Client\Credentials\Container(
                new Client\Credentials\Basic('abc', 'def'),
            )
        );

        $this->api = (new APIResource())
            ->setClient($this->vonageClient->reveal())
            ->setIsHAL(false)
            ->setAuthHandler(new BasicQueryHandler())
            ->setBaseUrl('https://api.nexmo.com/ni/advanced');

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

        $this->vonageClient->send(Argument::that(function (\Laminas\Diactoros\Request $request) use ($responseName) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals('https://api.nexmo.com/ni/advanced/ni/advanced/json?number=12345&api_key=abc&api_secret=def', $uriString);
            return true;
        }))->willReturn($this->getResponse($responseName, 200));

        $response = $this->insightClient->advanced('12345');
        $this->assertInstanceOf(Advanced::class, $response);
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

    /**
     * This method gets the fixtures and wraps them in a Response object to mock the API
     */
    protected function getResponse(string $identifier, int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/responses/' . $identifier . '.json', 'rb'), $status);
    }
}
