<?php

declare(strict_types=1);

namespace VonageTest\SimSwap;

use Laminas\Diactoros\Request;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\SimSwap\Client as SimSwapClient;
use VonageTest\Traits\HTTPTestTrait;
use VonageTest\VonageTestCase;

class ClientTest extends VonageTestCase
{
    use HTTPTestTrait;

    protected ObjectProphecy $vonageClient;
    protected SimSwapClient $simSwapClient;
    protected APIResource $api;
    protected Client|ObjectProphecy $handlerClient;
    protected int $requestCount = 0;

    /**
     * Create the Message API Client, and mock the Vonage Client
     */
    public function setUp(): void
    {
        $this->responsesDirectory = __DIR__ . '/Fixtures/Responses';

        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getCredentials()->willReturn(
            new Client\Credentials\Container(new Client\Credentials\Gnp(
                '+346661113334',
                file_get_contents(__DIR__ . '/../Client/Credentials/test.key'),
                'def',
            ))
        );

        $revealedClient = $this->vonageClient->reveal();

        $handler = new Client\Credentials\Handler\SimSwapGnpHandler();
        $handler->setBaseUrl('https://api-eu.vonage.com/oauth2/bc-authorize');
        $handler->setTokenUrl('https://api-eu.vonage.com/oauth2/token');
        $handler->setClient($revealedClient);

        $this->api = (new APIResource())
            ->setClient($revealedClient)
            ->setAuthHandlers($handler)
            ->setBaseUrl('https://api-eu.vonage.com/camara/sim-swap/v040/');

        $this->simSwapClient = new SimSwapClient($this->api);
    }

    public function testHasSetupClientCorrectly(): void
    {
        $this->assertInstanceOf(SimSwapClient::class, $this->simSwapClient);
    }

    public function testWillCheckSimSwap(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->requestCount++;

            if ($this->requestCount == 1) {
                $uri = $request->getUri();
                $uriString = $uri->__toString();
                $this->assertEquals(
                    'https://api-eu.vonage.com/oauth2/bc-authorize',
                    $uriString
                );

                $headers = $request->getHeaders();
                $this->assertArrayHasKey('Authorization', $headers);

                $this->assertRequestFormBodyContains('login_hint', '+346661113334', $request);
                $this->assertRequestFormBodyContains('scope', 'dpv:FraudPreventionAndDetection#check-sim-swap', $request);
                return true;
            }

            if ($this->requestCount == 2) {
                $uri = $request->getUri();
                $uriString = $uri->__toString();
                $this->assertEquals(
                    'https://api-eu.vonage.com/oauth2/token',
                    $uriString
                );

                $this->assertRequestFormBodyContains('grant_type', 'urn:openid:params:grant-type:ciba', $request);
                $this->assertRequestFormBodyContains('auth_req_id', '0dadaeb4-7c79-4d39-b4b0-5a6cc08bf537', $request);
                return true;
            }

            if ($this->requestCount == 3) {
                $this->assertEquals('POST', $request->getMethod());

                $uri = $request->getUri();
                $uriString = $uri->__toString();
                $this->assertEquals(
                    'https://api-eu.vonage.com/camara/sim-swap/v040/check',
                    $uriString
                );

                $this->assertRequestJsonBodyContains('phoneNumber', '+346661113334', $request);
                $this->assertRequestJsonBodyContains('maxAge', 240, $request);
                return true;
            }
        }))->willReturn(
            $this->getResponse('../../../Client/Credentials/Handler/Fixtures/Responses/gnp-be-success'),
            $this->getResponse('../../../Client/Credentials/Handler/Fixtures/Responses/gnp-token-success'),
            $this->getResponse('simswap-check-success')
        );

        $response = $this->simSwapClient->checkSimSwap('+346661113334', 240);

        $this->assertTrue($response);

        $this->requestCount = 0;
    }

    public function testWillRetrieveSimSwapDate(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->requestCount++;

            if ($this->requestCount == 1) {
                $uri = $request->getUri();
                $uriString = $uri->__toString();
                $this->assertEquals(
                    'https://api-eu.vonage.com/oauth2/bc-authorize',
                    $uriString
                );

                $headers = $request->getHeaders();
                $this->assertArrayHasKey('Authorization', $headers);

                $this->assertRequestFormBodyContains('login_hint', '+346661113334', $request);
                $this->assertRequestFormBodyContains('scope', 'dpv:FraudPreventionAndDetection#retrieve-sim-swap-date', $request);
                return true;
            }

            if ($this->requestCount == 2) {
                $uri = $request->getUri();
                $uriString = $uri->__toString();
                $this->assertEquals(
                    'https://api-eu.vonage.com/oauth2/token',
                    $uriString
                );

                $this->assertRequestFormBodyContains('grant_type', 'urn:openid:params:grant-type:ciba', $request);
                $this->assertRequestFormBodyContains('auth_req_id', '0dadaeb4-7c79-4d39-b4b0-5a6cc08bf537', $request);
                return true;
            }

            if ($this->requestCount == 3) {
                $this->assertEquals('POST', $request->getMethod());

                $uri = $request->getUri();
                $uriString = $uri->__toString();
                $this->assertEquals(
                    'https://api-eu.vonage.com/camara/sim-swap/v040/retrieve-date',
                    $uriString
                );

                $this->assertRequestJsonBodyContains('phoneNumber', '+346661113334', $request);
                return true;
            }
        }))->willReturn(
            $this->getResponse('../../../Client/Credentials/Handler/Fixtures/Responses/gnp-be-success'),
            $this->getResponse('../../../Client/Credentials/Handler/Fixtures/Responses/gnp-token-success'),
            $this->getResponse('simswap-date-success')
        );

        $response = $this->simSwapClient->checkSimSwapDate('+346661113334');

        $this->assertEquals('2019-08-24T14:15:22Z', $response);

        $this->requestCount = 0;
    }
}
