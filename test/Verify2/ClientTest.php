<?php

declare(strict_types=1);

namespace VonageTest\Verify2;

use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Vonage\Client\APIResource;
use Vonage\Messages\Channel\SMS\SMSText;
use Vonage\Verify2\Request\SMSRequest;
use Vonage\Verify2\VerifyObjects\VerificationLocale;
use VonageTest\Psr7AssertionTrait;
use VonageTest\VonageTestCase;
use Vonage\Client;
use Vonage\Verify2\Client as Verify2Client;

class ClientTest extends VonageTestCase
{
    use Psr7AssertionTrait;

    protected ObjectProphecy $vonageClient;
    protected Verify2Client $verify2Client;
    protected APIResource $api;

    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getRestUrl()->willReturn('https://rest.nexmo.com');
        $this->vonageClient->getCredentials()->willReturn(
            new Client\Credentials\Container(
                new Client\Credentials\Basic('abc', 'def'),
            )
        );

        /** @noinspection PhpParamsInspection */
        $this->api = (new APIResource())
            ->setIsHAL(false)
            ->setErrorsOn200(false)
            ->setClient($this->vonageClient->reveal())
            ->setAuthHandler([new Client\Credentials\Handler\BasicHandler(), new Client\Credentials\Handler\KeypairHandler()])
            ->setBaseUrl('https://rest.nexmo.com');

        $this->verify2Client = new Verify2Client($this->api);
    }

    public function testHasSetupClientCorrectly(): void
    {
        $this->assertInstanceOf(Verify2Client::class, $this->verify2Client);
    }

    public function testSetsRequestAuthCorrectly(): void
    {
        $payload = [
            'to' => '07785254785',
            'client_ref' => 'my-verification',
            'brand' => 'my-brand',
        ];

        $smsVerification = new SMSRequest($payload['to'], $payload['brand'], $payload['client_ref']);

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertEquals(
                'Basic ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 6)
            );
            return true;
        }))->willReturn($this->getResponse('sms-success', 202));

        $result = $this->verify2Client->send($smsVerification);
    }

    public function testCanRequestSMS(): void
    {
        $payload = [
            'to' => '07785254785',
            'client_ref' => 'my-verification',
            'brand' => 'my-brand',
        ];

        $smsVerification = new SMSRequest($payload['to'], $payload['brand'], $payload['client_ref']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertEquals(
                'Basic ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 6)
            );

            $this->assertRequestJsonBodyContains('locale', 'en-us', $request);
            $this->assertRequestJsonBodyContains('channel_timeout', 300, $request);
            $this->assertRequestJsonBodyContains('client_ref', $payload['client_ref'], $request);
            $this->assertRequestJsonBodyContains('code_length', 4, $request);
            $this->assertRequestJsonBodyContains('brand', $payload['brand'], $request);
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request, true);
            $this->assertRequestJsonBodyContains('channel', 'sms', $request, true);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));

        $result = $this->verify2Client->send($smsVerification);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('request_id', $result);
    }

    /**
     * @dataProvider localeProvider
     */
    public function testCannotRequestSMSWithInvalidLocale($locale, $valid): void
    {
        if (!$valid) {
            $this->expectException(\InvalidArgumentException::class);
        }

        $verificationLocale = new VerificationLocale($locale);

        $payload = [
            'to' => '07785254785',
            'client_ref' => 'my-verification',
            'brand' => 'my-brand',
            'locale' => $verificationLocale,
        ];

        $smsVerification = new SMSRequest($payload['to'], $payload['brand'], $payload['client_ref'], $payload['locale']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertEquals(
                'Basic ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 6)
            );

            $this->assertRequestJsonBodyContains('locale', $payload['locale']->getCode(), $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));

        $result = $this->verify2Client->send($smsVerification);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('request_id', $result);
    }

    /**
     * @dataProvider timeoutProvider
     */
    public function testTimeoutParsesCorrectly($timeout, $valid): void
    {
        if (!$valid) {
            $this->expectException(\OutOfBoundsException::class);
        }

        $payload = [
            'to' => '07785254785',
            'client_ref' => 'my-verification',
            'brand' => 'my-brand',
            'timeout' => $timeout
        ];

        $smsVerification = new SMSRequest($payload['to'], $payload['brand'], $payload['client_ref']);
        $smsVerification->setTimeout($timeout);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertEquals(
                'Basic ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 6)
            );

            $this->assertRequestJsonBodyContains('channel_timeout', $payload['timeout'], $request);

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));

        $result = $this->verify2Client->send($smsVerification);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('request_id', $result);
    }

    /**
     * @dataProvider PinLengthProvider
     */
    public function testCannotRequestSMSWithInvalidCodeLength(): void
    {

    }

    public function testCanRequestWhatsApp(): void
    {

    }

    public function testCanRequestWhatsAppInteractive(): void
    {

    }

    public function testCanRequestVoice(): void
    {

    }

    public function testCanRequestEmail(): void
    {

    }

    public function testCanRequestSilentAuth(): void
    {

    }

    public function testCannotSendConcurrentVerifications(): void
    {

    }

    public function testCannotSendWithoutBrand(): void
    {

    }

    public function testCanHandleThrottle(): void
    {

    }

    public function testCheckValidIdAndPIN(): void
    {

    }

    public function testCheckHandlesInvalidPIN(): void
    {

    }

    public function testCheckHandlesInvalidRequestId(): void
    {

    }

    public function testCheckHandlesConflict(): void
    {

    }

    public function testCheckHandlesLockedCodeSubmission(): void
    {

    }

    public function testCheckHandlesThrottle(): void
    {

    }

    public function testSilentAuthDoesNotAcceptPin(): void
    {

    }

    /**
     * This method gets the fixtures and wraps them in a Response object to mock the API
     */
    protected function getResponse(string $identifier, int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/Fixtures/Responses/' . $identifier . '.json', 'rb'), $status);
    }

    public function localeProvider(): array
    {
        return [
            ['en-us', true],
            ['en-gb', true],
            ['es-es', true],
            ['es-mx', true],
            ['es-us', true],
            ['it-it', true],
            ['en-id', false],
            ['pt-pr', false],
            ['be-pr', false],
            ['fr-fr', true],
            ['de-de', true],
            ['ru-ru', true],
            ['hi-in', true],
            ['pt-br', true],
            ['pt-pt', true],
            ['id-id', true]
        ];
    }

    public function pinLengthProvider(): array
    {
        return [];
    }

    public function timeoutProvider(): array
    {
        return [
            [60, true],
            [900, true],
            [59, false],
            [564, true],
            [921, false],
        ];
    }
}
