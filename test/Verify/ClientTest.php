<?php

declare(strict_types=1);

namespace VonageTest\Verify;

use Laminas\Diactoros\Response;
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Client;
use Vonage\Client\Exception\ServerException as ServerException;
use Vonage\Verify\Client as VerifyClient;
use Vonage\Verify\ExceptionErrorHandler;
use Vonage\Verify\Request;
use Vonage\Verify\RequestPSD2;
use Vonage\Verify\StartPSD2;
use Vonage\Verify\StartVerification;
use Vonage\Verify\Verification;
use VonageTest\Traits\HTTPTestTrait;
use VonageTest\Traits\Psr7AssertionTrait;
use VonageTest\VonageTestCase;

use function serialize;

class ClientTest extends VonageTestCase
{
    use Psr7AssertionTrait;
    use HTTPTestTrait;

    /**
     * @var VerifyClient
     */
    protected $client;

    protected $vonageClient;

    /**
     * Create the Message API Client, and mock the Vonage Client
     */
    public function setUp(): void
    {
        $this->responsesDirectory = __DIR__ . '/responses';

        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');
        $this->vonageClient->getCredentials()->willReturn(
            new Client\Credentials\Basic('abc', 'def'),
        );

        $api = new Client\APIResource($this->vonageClient->reveal());
        $api
            ->setIsHAL(false)
            ->setBaseUri('/verify')
            ->setErrorsOn200(true)
            ->setAuthHandlers(new Client\Credentials\Handler\BasicHandler())
            ->setExceptionErrorHandler(new ExceptionErrorHandler());

        $this->client = new VerifyClient($api);
    }

    public function testUnserializeAcceptsObject(): void
    {
        $mock = @$this->getMockBuilder(Verification::class)
            ->setConstructorArgs(['14845551212', 'Test Verify'])
            ->setMethods(['setClient'])
            ->getMock();

        $mock->expects(self::once())->method('setClient')->with($this->client);

        @$this->client->unserialize($mock);
    }

    /**
     * @param $response
     */
    public function testCanStartVerificationWithStartVerificationObject(): void
    {
        $this->vonageClient->send(
            Argument::that(
                function (RequestInterface $request) {
                    $this->assertRequestJsonBodyContains('number', '14845551212', $request);
                    $this->assertRequestJsonBodyContains('brand', 'Test Verify', $request);
                    $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/json', $request);

                    return true;
                }
            )
        )->willReturn($this->getResponse('start'))
            ->shouldBeCalledTimes(1);

        $request = new StartVerification('14845551212', 'Test Verify');
        $requestId = $this->client->startVerification($request);

        $this->assertSame('44a5279b27dd4a638d614d265ad57a77', $requestId);
    }

    public function testCanStartPsd2VerificationWithStartPsd2Object(): void
    {
        $this->vonageClient->send(
            Argument::that(
                function (RequestInterface $request) {
                    $this->assertRequestJsonBodyContains('number', '14845551212', $request);
                    $this->assertRequestJsonBodyContains('payee', 'Test Verify', $request);
                    $this->assertRequestJsonBodyContains('amount', '5.25', $request);
                    $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/psd2/json', $request);

                    return true;
                }
            )
        )->willReturn($this->getResponse('start'))
            ->shouldBeCalledTimes(1);

        $request = new StartPSD2('14845551212', 'Test Verify', '5.25');
        $requestId = $this->client->startPsd2Verification($request);

        $this->assertSame('44a5279b27dd4a638d614d265ad57a77', $requestId);
    }

    public function testCanTriggerNextEvent(): void
    {
        $this->vonageClient->send(
            Argument::that(
                function (RequestInterface $request) {
                    $this->assertRequestJsonBodyContains('request_id', '44a5279b27dd4a638d614d265ad57a77', $request);
                    $this->assertRequestJsonBodyContains('cmd', 'trigger_next_event', $request);
                    $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/control/json', $request);

                    return true;
                }
            )
        )->willReturn($this->getResponse('trigger'))
            ->shouldBeCalledTimes(1);

        $result = $this->client->triggerNextEvent('44a5279b27dd4a638d614d265ad57a77');

        $this->assertTrue($result);
    }

    protected function setupClientForStart($response): Response
    {
        $response = $this->getResponse($response);
        $this->vonageClient->send(
            Argument::that(
                function (RequestInterface $request) {
                    $this->assertRequestJsonBodyContains('number', '14845551212', $request);
                    $this->assertRequestJsonBodyContains('brand', 'Test Verify', $request);
                    $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/json', $request);

                    return true;
                }
            )
        )->willReturn($response)
            ->shouldBeCalledTimes(1);

        return $response;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     * @throws ServerException
     */
    public function testCanSearchId(): void
    {
        $response = $this->setupClientForSearch('search');

        $verification = $this->client->search('44a5279b27dd4a638d614d265ad57a77');

        $this->assertSame('44a5279b27dd4a638d614d265ad57a77', $verification->getRequestId());
    }

    /**
     * @param $response
     */
    protected function setupClientForSearch($response): Response
    {
        $response = $this->getResponse($response);
        $this->vonageClient->send(
            Argument::that(
                function (RequestInterface $request) {
                    $this->assertRequestJsonBodyContains('request_id', '44a5279b27dd4a638d614d265ad57a77', $request);
                    $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/search/json', $request);

                    return true;
                }
            )
        )->willReturn($response)
            ->shouldBeCalledTimes(1);

        return $response;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     * @throws ServerException
     */
    public function testCanCancelId(): void
    {
        $response = $this->setupClientForControl('cancel', 'cancel');

        $verification = @$this->client->cancel('44a5279b27dd4a638d614d265ad57a77');

        $this->assertSame('44a5279b27dd4a638d614d265ad57a77', $verification->getRequestId());
    }

    /**
     * @param $response
     * @param $cmd
     */
    protected function setupClientForControl($response, $cmd): Response
    {
        $response = $this->getResponse($response);
        $this->vonageClient->send(
            Argument::that(
                function (RequestInterface $request) use ($cmd) {
                    $this->assertRequestJsonBodyContains('request_id', '44a5279b27dd4a638d614d265ad57a77', $request);
                    $this->assertRequestJsonBodyContains('cmd', $cmd, $request);
                    $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/control/json', $request);

                    return true;
                }
            )
        )->willReturn($response)
            ->shouldBeCalledTimes(1);

        return $response;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     * @throws ServerException
     */
    public function testCanCheckId(): void
    {
        $response = $this->setupClientForCheck('check', '1234');
        $verification = $this->client->check('44a5279b27dd4a638d614d265ad57a77', '1234');

        $this->assertSame('de37150c89584f36a18925181d62627c', $verification->getRequestId());
    }

    /**
     * @param $response
     * @param $code
     */
    protected function setupClientForCheck($response, $code, ?string $ip = null): Response
    {
        $response = $this->getResponse($response);

        $this->vonageClient->send(
            Argument::that(
                function (RequestInterface $request) use ($code, $ip) {
                    $this->assertRequestJsonBodyContains('request_id', '44a5279b27dd4a638d614d265ad57a77', $request);
                    $this->assertRequestJsonBodyContains('code', $code, $request);

                    if ($ip) {
                        $this->assertRequestJsonBodyContains('ip_address', $ip, $request);
                    }

                    $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/check/json', $request);
                    return true;
                }
            )
        )->willReturn($response)
            ->shouldBeCalledTimes(1);

        return $response;
    }
}
