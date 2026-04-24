<?php

declare(strict_types=1);

namespace VonageTest\Verify;

use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Vonage\Client;
use Vonage\Client\Exception\Request as RequestException;
use Vonage\Entity\IterableAPICollection;
use Vonage\Verify\Check;
use Vonage\Verify\CheckAttempt;
use Vonage\Verify\Client as VerifyClient;
use Vonage\Verify\ExceptionErrorHandler;
use Vonage\Verify\StartPSD2;
use Vonage\Verify\StartVerification;
use Vonage\Verify\Verification;
use VonageTest\Traits\HTTPTestTrait;
use VonageTest\Traits\Psr7AssertionTrait;
use VonageTest\VonageTestCase;

class ClientTest extends VonageTestCase
{
    use Psr7AssertionTrait;
    use HTTPTestTrait;

    protected VerifyClient $client;
    protected $vonageClient;
    protected $httpClient;

    public function setUp(): void
    {
        $this->responsesDirectory = __DIR__ . '/responses';

        $this->vonageClient = $this->prophesize(Client::class);
        $this->httpClient = $this->prophesize(\Psr\Http\Client\ClientInterface::class);
        $this->vonageClient->getHttpClient()->willReturn($this->httpClient->reveal());
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

    public function testUsesBasicAuth(): void
    {
        $this->httpClient->sendRequest(
            Argument::that(function (RequestInterface $request) {
                $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/json', $request);
                $this->assertStringStartsWith('Basic ', $request->getHeaders()['Authorization'][0]);
                return true;
            })
        )->willReturn($this->getResponse('start'))
            ->shouldBeCalledTimes(1);

        $this->client->startVerification(new StartVerification('14845551212', 'Test Verify'));
    }

    // -------------------------------------------------------------------------
    // startVerification
    // -------------------------------------------------------------------------

    public function testStartVerificationReturnsRequestId(): void
    {
        $this->httpClient->sendRequest(
            Argument::that(function (RequestInterface $request) {
                $this->assertRequestJsonBodyContains('number', '14845551212', $request);
                $this->assertRequestJsonBodyContains('brand', 'Test Verify', $request);
                $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/json', $request);
                return true;
            })
        )->willReturn($this->getResponse('start'))
            ->shouldBeCalledTimes(1);

        $requestId = $this->client->startVerification(new StartVerification('14845551212', 'Test Verify'));

        $this->assertIsString($requestId);
        $this->assertSame('44a5279b27dd4a638d614d265ad57a77', $requestId);
    }

    public function testStartVerificationThrowsOnError(): void
    {
        $this->expectException(RequestException::class);

        $this->httpClient->sendRequest(Argument::any())
            ->willReturn($this->getResponse('start-error'));

        $this->client->startVerification(new StartVerification('14845551212', 'Test Verify'));
    }

    public function testStartVerificationThrowsOnConcurrentError(): void
    {
        $this->expectException(RequestException::class);

        $this->httpClient->sendRequest(Argument::any())
            ->willReturn($this->getResponse('start-error-concurrent'));

        $this->client->startVerification(new StartVerification('14845551212', 'Test Verify'));
    }

    public function testStartVerificationThrowsOnBlockedNumber(): void
    {
        $this->expectException(RequestException::class);

        $this->httpClient->sendRequest(Argument::any())
            ->willReturn($this->getResponse('start-error-blocked'));

        $this->client->startVerification(new StartVerification('14845551212', 'Test Verify'));
    }

    // -------------------------------------------------------------------------
    // startPsd2Verification
    // -------------------------------------------------------------------------

    public function testStartPsd2VerificationReturnsRequestId(): void
    {
        $this->httpClient->sendRequest(
            Argument::that(function (RequestInterface $request) {
                $this->assertRequestJsonBodyContains('number', '14845551212', $request);
                $this->assertRequestJsonBodyContains('payee', 'Test Payee', $request);
                $this->assertRequestJsonBodyContains('amount', '5.25', $request);
                $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/psd2/json', $request);
                return true;
            })
        )->willReturn($this->getResponse('start'))
            ->shouldBeCalledTimes(1);

        $requestId = $this->client->startPsd2Verification(new StartPSD2('14845551212', 'Test Payee', '5.25'));

        $this->assertIsString($requestId);
        $this->assertSame('44a5279b27dd4a638d614d265ad57a77', $requestId);
    }

    public function testStartPsd2VerificationWithWorkflowId(): void
    {
        $this->httpClient->sendRequest(
            Argument::that(function (RequestInterface $request) {
                $this->assertRequestJsonBodyContains('workflow_id', 5, $request);
                $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/psd2/json', $request);
                return true;
            })
        )->willReturn($this->getResponse('start'))
            ->shouldBeCalledTimes(1);

        $this->client->startPsd2Verification(new StartPSD2('14845551212', 'Test Payee', '5.25', 5));
    }

    public function testPsd2UsesBasicAuth(): void
    {
        $this->httpClient->sendRequest(
            Argument::that(function (RequestInterface $request) {
                $this->assertStringStartsWith('Basic ', $request->getHeaders()['Authorization'][0]);
                return true;
            })
        )->willReturn($this->getResponse('start'))
            ->shouldBeCalledTimes(1);

        $this->client->startPsd2Verification(new StartPSD2('14845551212', 'Test Payee', '5.25'));
    }

    // -------------------------------------------------------------------------
    // get (single)
    // -------------------------------------------------------------------------

    public function testGetReturnsVerification(): void
    {
        $this->httpClient->sendRequest(
            Argument::that(function (RequestInterface $request) {
                $this->assertRequestMethod('GET', $request);
                $this->assertRequestQueryContains('request_id', '44a5279b27dd4a638d614d265ad57a77', $request);
                $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/search/json', $request);
                return true;
            })
        )->willReturn($this->getResponse('search'))
            ->shouldBeCalledTimes(1);

        $verification = $this->client->get('44a5279b27dd4a638d614d265ad57a77');

        $this->assertInstanceOf(Verification::class, $verification);
        $this->assertSame('44a5279b27dd4a638d614d265ad57a77', $verification->requestId);
        $this->assertSame('FAILED', $verification->status);
    }

    public function testGetHydratesCheckAttempts(): void
    {
        $this->httpClient->sendRequest(Argument::any())
            ->willReturn($this->getResponse('search'));

        $verification = $this->client->get('44a5279b27dd4a638d614d265ad57a77');

        $checks = $verification->checks;
        $this->assertCount(3, $checks);
        $this->assertContainsOnlyInstancesOf(CheckAttempt::class, $checks);
    }

    public function testGetThrowsOnError(): void
    {
        $this->expectException(RequestException::class);

        $this->httpClient->sendRequest(Argument::any())
            ->willReturn($this->getResponse('search-error'));

        $this->client->get('44a5279b27dd4a638d614d265ad57a77');
    }

    // -------------------------------------------------------------------------
    // search (bulk)
    // -------------------------------------------------------------------------

    public function testSearchReturnsBulkVerifications(): void
    {
        $this->httpClient->sendRequest(
            Argument::that(function (RequestInterface $request) {
                $this->assertRequestMethod('GET', $request);
                $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/search/json', $request);
                return true;
            })
        )->willReturn($this->getResponse('search-bulk'))
            ->shouldBeCalledTimes(1);

        $results = $this->client->search([
            '44a5279b27dd4a638d614d265ad57a77',
            'c1878c7451f94c1992d52797df57658e',
        ]);

        $this->assertInstanceOf(IterableAPICollection::class, $results);

        $items = iterator_to_array($results);
        $this->assertCount(2, $items);
        $this->assertContainsOnlyInstancesOf(Verification::class, $items);
        $this->assertSame('44a5279b27dd4a638d614d265ad57a77', $items[0]->requestId);
        $this->assertSame('c1878c7451f94c1992d52797df57658e', $items[1]->requestId);
    }

    // -------------------------------------------------------------------------
    // check
    // -------------------------------------------------------------------------

    public function testCheckReturnsCheckDto(): void
    {
        $this->httpClient->sendRequest(
            Argument::that(function (RequestInterface $request) {
                $this->assertRequestJsonBodyContains('request_id', '44a5279b27dd4a638d614d265ad57a77', $request);
                $this->assertRequestJsonBodyContains('code', '1234', $request);
                $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/check/json', $request);
                return true;
            })
        )->willReturn($this->getResponse('check'))
            ->shouldBeCalledTimes(1);

        $result = $this->client->check('44a5279b27dd4a638d614d265ad57a77', '1234');

        $this->assertInstanceOf(Check::class, $result);
        $this->assertSame('de37150c89584f36a18925181d62627c', $result->requestId);
        $this->assertSame('0', $result->status);
        $this->assertSame('0.10000000', $result->price);
        $this->assertSame('EUR', $result->currency);
    }

    public function testCheckThrowsOnInvalidCode(): void
    {
        $this->expectException(RequestException::class);

        $this->httpClient->sendRequest(Argument::any())
            ->willReturn($this->getResponse('check-error'));

        $this->client->check('44a5279b27dd4a638d614d265ad57a77', '9999');
    }

    // -------------------------------------------------------------------------
    // cancel
    // -------------------------------------------------------------------------

    public function testCancelReturnsTrue(): void
    {
        $this->httpClient->sendRequest(
            Argument::that(function (RequestInterface $request) {
                $this->assertRequestJsonBodyContains('request_id', '44a5279b27dd4a638d614d265ad57a77', $request);
                $this->assertRequestJsonBodyContains('cmd', 'cancel', $request);
                $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/control/json', $request);
                return true;
            })
        )->willReturn($this->getResponse('cancel'))
            ->shouldBeCalledTimes(1);

        $result = $this->client->cancel('44a5279b27dd4a638d614d265ad57a77');
        $this->assertTrue($result);
    }

    public function testCancelThrowsOnError(): void
    {
        $this->expectException(RequestException::class);

        $this->httpClient->sendRequest(Argument::any())
            ->willReturn($this->getResponse('cancel-error'));

        $this->client->cancel('c1878c7451f94c1992d52797df57658e');
    }

    // -------------------------------------------------------------------------
    // triggerNextEvent
    // -------------------------------------------------------------------------

    public function testTriggerNextEventReturnsTrue(): void
    {
        $this->httpClient->sendRequest(
            Argument::that(function (RequestInterface $request) {
                $this->assertRequestJsonBodyContains('request_id', '44a5279b27dd4a638d614d265ad57a77', $request);
                $this->assertRequestJsonBodyContains('cmd', 'trigger_next_event', $request);
                $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/control/json', $request);
                return true;
            })
        )->willReturn($this->getResponse('trigger'))
            ->shouldBeCalledTimes(1);

        $result = $this->client->triggerNextEvent('44a5279b27dd4a638d614d265ad57a77');
        $this->assertTrue($result);
    }

    public function testTriggerNextEventThrowsOnError(): void
    {
        $this->expectException(RequestException::class);

        $this->httpClient->sendRequest(Argument::any())
            ->willReturn($this->getResponse('trigger-error'));

        $this->client->triggerNextEvent('44a5279b27dd4a638d614d265ad57a77');
    }
}

