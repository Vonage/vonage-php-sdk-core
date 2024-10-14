<?php

namespace VonageTest\Client\Credentials\Handler;

use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Vonage\Client;
use Vonage\Client\Credentials\Gnp;
use PHPUnit\Framework\TestCase;
use Vonage\Client\Credentials\Handler\SimSwapGnpHandler;
use VonageTest\Traits\Psr7AssertionTrait;

class GnpHandlerTest extends TestCase
{
    use ProphecyTrait;
    use Psr7AssertionTrait;

    protected string|false $key;
    protected string $application = 'c90ddd99-9a5d-455f-8ade-dde4859e590e';
    protected string $msisdn = 'tel:+447700900000';
    protected Client|ObjectProphecy $handlerClient;
    protected int $requestCount = 0;

    public function setUp(): void
    {
        $this->key = file_get_contents(__DIR__ . '/../test.key');

        $this->handlerClient = $this->prophesize(Client::class);
        $this->handlerClient->getCredentials()->willReturn(
            new Client\Credentials\Container(new Client\Credentials\Gnp(
                'tel:+447700900000',
                $this->key,
                $this->application
            ))
        );
    }

    public function time(): int
    {
        return 1697209080;
    }

    public function testWillReturnSimSwapCheckWithValidCredentials(): void
    {
        $this->handlerClient->send(Argument::that(function (Request $request) {
            $this->requestCount++;

            if ($this->requestCount === 1) {
                $this->assertRequestMethod('POST', $request);

                $uri = $request->getUri();
                $uriString = $uri->__toString();
                $this->assertEquals(
                    'https://api-eu.vonage.com/oauth2/bc-authorize',
                    $uriString
                );

                $this->assertRequestFormBodyContains('login_hint', 'tel:+447700900000', $request);
                $this->assertRequestFormBodyContains(
                    'scope',
                    'dpv:FraudPreventionAndDetection#check-sim-swap',
                    $request
                );

                $this->assertEquals(
                    'Bearer ',
                    mb_substr($request->getHeaders()['Authorization'][0], 0, 7)
                );

                return true;
            }

            if ($this->requestCount === 2) {
                $this->assertEquals('POST', $request->getMethod());

                $uri = $request->getUri();
                $uriString = $uri->__toString();
                $this->assertEquals(
                    'https://api-eu.vonage.com/oauth2/token',
                    $uriString
                );

                $this->assertRequestFormBodyContains(
                    'grant_type',
                    'urn:openid:params:grant-type:ciba',
                    $request
                );

                $this->assertRequestFormBodyContains(
                    'auth_req_id',
                    '0dadaeb4-7c79-4d39-b4b0-5a6cc08bf537',
                    $request
                );

                return true;
            }
        }))->willReturn($this->getResponse('gnp-be-success'), $this->getResponse('gnp-token-success'));

        $credentials = new Gnp($this->msisdn, $this->key, $this->application);
        $handler = new Client\Credentials\Handler\SimSwapGnpHandler();
        $handler->setBaseUrl('https://api-eu.vonage.com/oauth2/bc-authorize');
        $handler->setTokenUrl('https://api-eu.vonage.com/oauth2/token');

        $handler->setClient($this->handlerClient->reveal());
        $handler->setScope('dpv:FraudPreventionAndDetection#check-sim-swap');

        $request = new Request();

        $request = $handler($request, $credentials);
        $authHeader = $request->getHeader('Authorization');
        $this->assertEquals('Bearer 5d0358cd-8861-4c9f-9702-6164b6e483f4', $authHeader[0]);

        $this->assertInstanceOf(Request::class, $request);
    }

    public function testWillReturnSimSwapDateWithValidCredentials(): void
    {
        $this->handlerClient->send(Argument::that(function (Request $request) {
            $this->requestCount++;

            if ($this->requestCount === 1) {
                $this->assertRequestMethod('POST', $request);

                $uri = $request->getUri();
                $uriString = $uri->__toString();
                $this->assertEquals(
                    'https://api-eu.vonage.com/oauth2/bc-authorize',
                    $uriString
                );

                $this->assertRequestFormBodyContains(
                    'login_hint',
                    'tel:+447700900000',
                    $request
                );

                $this->assertRequestFormBodyContains(
                    'scope',
                    'dpv:FraudPreventionAndDetection#check-sim-swap',
                    $request
                );

                $this->assertEquals(
                    'Bearer ',
                    mb_substr($request->getHeaders()['Authorization'][0], 0, 7)
                );

                return true;
            }

            if ($this->requestCount === 2) {
                $this->assertEquals('POST', $request->getMethod());

                $uri = $request->getUri();
                $uriString = $uri->__toString();
                $this->assertEquals(
                    'https://api-eu.vonage.com/oauth2/token',
                    $uriString
                );

                $this->assertRequestFormBodyContains(
                    'grant_type',
                    'urn:openid:params:grant-type:ciba',
                    $request
                );

                $this->assertRequestFormBodyContains(
                    'auth_req_id',
                    '0dadaeb4-7c79-4d39-b4b0-5a6cc08bf537',
                    $request
                );

                return true;
            }
        }))->willReturn($this->getResponse('gnp-be-success'), $this->getResponse('gnp-token-success'));

        $credentials = new Gnp($this->msisdn, $this->key, $this->application);
        $handler = new SimSwapGnpHandler();
        $handler->setBaseUrl('https://api-eu.vonage.com/oauth2/bc-authorize');
        $handler->setTokenUrl('https://api-eu.vonage.com/oauth2/token');

        $handler->setClient($this->handlerClient->reveal());
        $handler->setScope('dpv:FraudPreventionAndDetection#check-sim-swap');

        $request = new Request();

        $request = $handler($request, $credentials);
        $this->assertInstanceOf(Request::class, $request);
    }
    protected function getResponse(string $identifier, int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/Fixtures/Responses/' . $identifier . '.json', 'rb'), $status);
    }
}
