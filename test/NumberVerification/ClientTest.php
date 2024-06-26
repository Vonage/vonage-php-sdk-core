<?php

declare(strict_types=1);

namespace VonageTest\NumberVerification;

use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Vonage\Client\APIResource;
use VonageTest\Psr7AssertionTrait;
use VonageTest\VonageTestCase;
use Vonage\Client;
use Vonage\SimSwap\Client as SimSwapClient;

class ClientTest extends VonageTestCase
{
    use Psr7AssertionTrait;

    protected ObjectProphecy $vonageClient;
    protected SimSwapClient $simSwapClient;
    protected APIResource $api;
    protected Client|ObjectProphecy $handlerClient;

    /**
     * Create the Message API Client, and mock the Vonage Client
     */
    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getCredentials()->willReturn(
            new Client\Credentials\Container(new Client\Credentials\Gnp(
                'tel:+447700900000',
                file_get_contents(__DIR__ . '/../Client/Credentials/test.key'),
                'def',
            ))
        );

        $this->handlerClient = $this->prophesize(Client::class);
        $this->handlerClient->getCredentials()->willReturn(
            new Client\Credentials\Container(new Client\Credentials\Gnp(
                'tel:+447700900000',
                file_get_contents(__DIR__ . '/../Client/Credentials/test.key'),
                'def',
            ))
        );

        $handler = new Client\Credentials\Handler\SimSwapGnpHandler();
        $handler->setClient($this->handlerClient->reveal());

        /** @noinspection PhpParamsInspection */
        $this->api = (new APIResource())
            ->setClient($this->vonageClient->reveal())
            ->setAuthHandlers($handler)
            ->setBaseUrl('https://api-eu.vonage.com/camara/sim-swap/v040/');

        $this->simSwapClient = new SimSwapClient($this->api);
    }

    public function testHasSetupClientCorrectly(): void
    {
        $this->assertInstanceOf(SimSwapClient::class, $this->simSwapClient);
    }

    public function testWillCheckNumberSuccessfully(): void
    {
        $this->handlerClient->send(Argument::that(function (Request $request) {
        }))->willReturn(
            $this->getResponse('../../../Client/Credentials/Handler/Fixtures/Responses/gnp-be-success'),
            $this->getResponse('../../../Client/Credentials/Handler/Fixtures/Responses/gnp-token-success')
        );

        $this->vonageClient->send(Argument::that(function (Request $request) {
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
        }))->willReturn($this->getResponse('ni-check-success'));

        $response = $this->simSwapClient->checkSimSwap('+346661113334', 240);

        $this->assertTrue($response);
    }

    public function testWillCheckNumberThatFails(): void
    {
        $this->handlerClient->send(Argument::that(function (Request $request) {
        }))->willReturn(
            $this->getResponse('../../../Client/Credentials/Handler/Fixtures/Responses/gnp-be-success'),
            $this->getResponse('../../../Client/Credentials/Handler/Fixtures/Responses/gnp-token-success')
        );

        $this->vonageClient->send(Argument::that(function (Request $request) {
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
        }))->willReturn($this->getResponse('ni-check-failed'));

        $response = $this->simSwapClient->checkSimSwap('+346661113334', 240);

        $this->assertFalse($response);
    }

    /**
     * This method gets the fixtures and wraps them in a Response object to mock the API
     */
    protected function getResponse(string $identifier, int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/Fixtures/Responses/' . $identifier . '.json', 'rb'), $status);
    }
}
