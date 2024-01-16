<?php

declare(strict_types=1);

namespace VonageTest\Verify2;

use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Vonage\Client\APIResource;
use Vonage\Verify2\Request\EmailRequest;
use Vonage\Verify2\Request\SilentAuthRequest;
use Vonage\Verify2\Request\SMSRequest;
use Vonage\Verify2\Request\VoiceRequest;
use Vonage\Verify2\Request\WhatsAppInteractiveRequest;
use Vonage\Verify2\Request\WhatsAppRequest;
use Vonage\Verify2\VerifyObjects\VerificationLocale;
use Vonage\Verify2\VerifyObjects\VerificationWorkflow;
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
        $this->vonageClient->getRestUrl()->willReturn('https://api.nexmo.com');
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
            ->setBaseUrl('https://api.nexmo.com/v2/verify');

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

        $smsVerification = new SMSRequest($payload['to'], $payload['brand']);
        $smsVerification->setClientRef($payload['client_ref']);

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertEquals(
                'Basic ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 6)
            );
            return true;
        }))->willReturn($this->getResponse('verify-request-success', 202));

        $result = $this->verify2Client->startVerification($smsVerification);
    }

    public function testCanRequestSMS(): void
    {
        $payload = [
            'to' => '07785254785',
            'client_ref' => 'my-verification',
            'brand' => 'my-brand',
            'from' => 'vonage'
        ];

        $smsVerification = new SMSRequest($payload['to'], $payload['brand'], null, $payload['from']);
        $smsVerification->setClientRef($payload['client_ref']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v2/verify',
                $uriString
            );

            $this->assertRequestJsonBodyContains('locale', 'en-us', $request);
            $this->assertRequestJsonBodyMissing('fraud_check', $request);
            $this->assertRequestJsonBodyContains('channel_timeout', 300, $request);
            $this->assertRequestJsonBodyContains('client_ref', $payload['client_ref'], $request);
            $this->assertRequestJsonBodyContains('code_length', 4, $request);
            $this->assertRequestJsonBodyContains('brand', $payload['brand'], $request);
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request, true);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request, true);
            $this->assertRequestJsonBodyContains('channel', 'sms', $request, true);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('verify-request-success', 202));

        $result = $this->verify2Client->startVerification($smsVerification);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('request_id', $result);
    }

    public function testWillPopulateEntityIdAndContentId(): void
    {
        $payload = [
            'to' => '07785254785',
            'client_ref' => 'my-verification',
            'brand' => 'my-brand',
            'from' => 'vonage',
            'entity_id' => '1101407360000017170',
            'content_id' => '1107158078772563946'
        ];

        $smsVerification = new SMSRequest(
            $payload['to'],
            $payload['brand'],
            null,
            $payload['from'],
            $payload['entity_id'],
            $payload['content_id']
        );

        $smsVerification->setClientRef($payload['client_ref']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v2/verify',
                $uriString
            );

            $this->assertRequestJsonBodyContains('entity_id', '1101407360000017170', $request, true);
            $this->assertRequestJsonBodyContains('content_id', '1107158078772563946', $request, true);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('verify-request-success', 202));

        $result = $this->verify2Client->startVerification($smsVerification);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('request_id', $result);
    }

    public function testCanBypassFraudCheck(): void
    {
        $payload = [
            'to' => '07785254785',
            'brand' => 'my-brand',
        ];

        $smsVerification = new SMSRequest($payload['to'], $payload['brand']);
        $smsVerification->setFraudCheck(false);

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestJsonBodyContains('fraud_check', false, $request);

            return true;
        }))->willReturn($this->getResponse('verify-request-success', 202));

        $result = $this->verify2Client->startVerification($smsVerification);

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

        $smsVerification = new SMSRequest($payload['to'], $payload['brand']);
        $smsVerification->setClientRef($payload['client_ref']);
        $smsVerification->setTimeout($timeout);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertEquals(
                'Basic ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 6)
            );

            $this->assertRequestJsonBodyContains('channel_timeout', $payload['timeout'], $request);

            return true;
        }))->willReturn($this->getResponse('verify-request-success', 202));

        $result = $this->verify2Client->startVerification($smsVerification);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('request_id', $result);
    }

    /**
     * @dataProvider pinLengthProvider
     */
    public function testCannotRequestSMSWithInvalidCodeLength($length, $valid): void
    {
        if (!$valid) {
            $this->expectException(\OutOfBoundsException::class);
        }

        $payload = [
            'to' => '07785254785',
            'client_ref' => 'my-verification',
            'brand' => 'my-brand',
            'length' => $length
        ];

        $smsVerification = new SMSRequest($payload['to'], $payload['brand']);
        $smsVerification->setClientRef($payload['client_ref']);
        $smsVerification->setLength($payload['length']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('code_length', $payload['length'], $request);
            return true;
        }))->willReturn($this->getResponse('verify-request-success', 202));

        $result = $this->verify2Client->startVerification($smsVerification);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('request_id', $result);
    }

    public function testCanRequestWhatsApp(): void
    {
        $payload = [
            'to' => '07785254785',
            'client_ref' => 'my-verification',
            'brand' => 'my-brand',
        ];

        $whatsAppVerification = new WhatsAppRequest($payload['to'], $payload['brand']);
        $whatsAppVerification->setClientRef($payload['client_ref']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('locale', 'en-us', $request);
            $this->assertRequestJsonBodyContains('channel_timeout', 300, $request);
            $this->assertRequestJsonBodyContains('client_ref', $payload['client_ref'], $request);
            $this->assertRequestJsonBodyContains('code_length', 4, $request);
            $this->assertRequestJsonBodyContains('brand', $payload['brand'], $request);
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request, true);
            $this->assertRequestJsonBodyContains('channel', 'whatsapp', $request, true);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('verify-request-success', 202));

        $result = $this->verify2Client->startVerification($whatsAppVerification);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('request_id', $result);
    }

    public function testCanRequestWhatsAppInteractive(): void
    {
        $payload = [
            'to' => '07785254785',
            'client_ref' => 'my-verification',
            'brand' => 'my-brand',
        ];

        $whatsAppInteractiveRequest = new WhatsAppInteractiveRequest($payload['to'], $payload['brand']);
        $whatsAppInteractiveRequest->setClientRef($payload['client_ref']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('locale', 'en-us', $request);
            $this->assertRequestJsonBodyContains('channel_timeout', 300, $request);
            $this->assertRequestJsonBodyContains('client_ref', $payload['client_ref'], $request);
            $this->assertRequestJsonBodyContains('code_length', 4, $request);
            $this->assertRequestJsonBodyContains('brand', $payload['brand'], $request);
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request, true);
            $this->assertRequestJsonBodyContains('channel', 'whatsapp_interactive', $request, true);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('verify-request-success', 202));

        $result = $this->verify2Client->startVerification($whatsAppInteractiveRequest);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('request_id', $result);
    }

    public function testCanRequestVoice(): void
    {
        $payload = [
            'to' => '07785254785',
            'client_ref' => 'my-verification',
            'brand' => 'my-brand',
        ];

        $voiceRequest = new VoiceRequest($payload['to'], $payload['brand']);
        $voiceRequest->setClientRef($payload['client_ref']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('locale', 'en-us', $request);
            $this->assertRequestJsonBodyContains('channel_timeout', 300, $request);
            $this->assertRequestJsonBodyContains('client_ref', $payload['client_ref'], $request);
            $this->assertRequestJsonBodyContains('code_length', 4, $request);
            $this->assertRequestJsonBodyContains('brand', $payload['brand'], $request);
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request, true);
            $this->assertRequestJsonBodyContains('channel', 'voice', $request, true);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('verify-request-success', 202));

        $result = $this->verify2Client->startVerification($voiceRequest);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('request_id', $result);
    }

    public function testCanRequestEmail(): void
    {
        $payload = [
            'to' => 'alice@company.com',
            'from' => 'bob@company.com',
            'client_ref' => 'my-verification',
            'brand' => 'my-brand',
        ];

        $emailRequest = new EmailRequest($payload['to'], $payload['brand'], $payload['from']);
        $emailRequest->setClientRef($payload['client_ref']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('locale', 'en-us', $request);
            $this->assertRequestJsonBodyContains('channel_timeout', 300, $request);
            $this->assertRequestJsonBodyContains('client_ref', $payload['client_ref'], $request);
            $this->assertRequestJsonBodyContains('code_length', 4, $request);
            $this->assertRequestJsonBodyContains('brand', $payload['brand'], $request);
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request, true);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request, true);
            $this->assertRequestJsonBodyContains('channel', 'email', $request, true);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('verify-request-success', 202));

        $result = $this->verify2Client->startVerification($emailRequest);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('request_id', $result);
    }

    public function testCanRenderOptionalCode(): void
    {
        $payload = [
            'to' => '07785648870',
            'brand' => 'my-brand',
        ];

        $smsRequest = new SMSRequest($payload['to'], $payload['brand']);
        $smsRequest->setCode('123456789');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestJsonBodyContains('code', '123456789', $request, true);

            return true;
        }))->willReturn($this->getResponse('verify-request-success', 202));

        $result = $this->verify2Client->startVerification($smsRequest);
    }

    public function testCanHandleMultipleWorkflows(): void
    {
        $payload = [
            'to' => '07785254785',
            'client_ref' => 'my-verification',
            'brand' => 'my-brand',
        ];

        $smsVerification = new SMSRequest($payload['to'], $payload['brand']);
        $smsVerification->setClientRef($payload['client_ref']);
        $voiceWorkflow = new VerificationWorkflow('voice', '07785254785');
        $smsVerification->addWorkflow($voiceWorkflow);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('channel', 'sms', $request, true);
            $this->assertRequestJsonBodyContains('channel', 'voice', $request, true);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('verify-request-success', 202));

        $result = $this->verify2Client->startVerification($smsVerification);
    }

    public function testCanRequestSilentAuth(): void
    {
        $payload = [
            'to' => '07784587411',
            'brand' => 'my-brand',
        ];

        $silentAuthRequest = new SilentAuthRequest($payload['to'], $payload['brand']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('brand', $payload['brand'], $request);
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request, true);
            $this->assertRequestJsonBodyContains('channel', 'silent_auth', $request, true);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('verify-silent-auth-request-success', 202));

        $result = $this->verify2Client->startVerification($silentAuthRequest);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('request_id', $result);
        $this->assertArrayHasKey('check_url', $result);
        $this->assertEquals('https://api.nexmo.com/v2/verify/c11236f4-00bf-4b89-84ba-88b25df97315/silent-auth/redirect', $result['check_url']);
    }

    public function testCanRequestSilentAuthWithRedirectUrl(): void
    {
        $payload = [
            'to' => '07784587411',
            'brand' => 'my-brand',
            'redirect_url' => 'https://my-app-endpoint/webhook'
        ];

        $silentAuthRequest = new SilentAuthRequest($payload['to'], $payload['brand'], $payload['redirect_url']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('brand', $payload['brand'], $request);
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request, true);
            $this->assertRequestJsonBodyContains('channel', 'silent_auth', $request, true);
            $this->assertRequestJsonBodyContains('redirect_url', 'https://my-app-endpoint/webhook', $request, true);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('verify-silent-auth-request-success', 202));

        $result = $this->verify2Client->startVerification($silentAuthRequest);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('request_id', $result);
        $this->assertArrayHasKey('check_url', $result);
        $this->assertEquals('https://api.nexmo.com/v2/verify/c11236f4-00bf-4b89-84ba-88b25df97315/silent-auth/redirect', $result['check_url']);
    }

    public function testCannotSendConcurrentVerifications(): void
    {
        $this->expectException(Client\Exception\Request::class);
        $this->expectExceptionMessage('Conflict: Concurrent verifications to the same number are not allowed.. See https://www.developer.vonage.com/api-errors/verify#conflict for more information');

        $payload = [
            'to' => '07785254785',
            'client_ref' => 'my-verification',
            'brand' => 'my-brand',
        ];

        $smsVerification = new SMSRequest($payload['to'], $payload['brand']);
        $smsVerification->setClientRef($payload['client_ref']);

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertEquals('POST', $request->getMethod());
            return true;
        }))->willReturn(
            $this->getResponse('verify-request-success', 202),
            $this->getResponse('verify-request-conflict', 409)
        );

        $result = $this->verify2Client->startVerification($smsVerification);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('request_id', $result);

        $result = $this->verify2Client->startVerification($smsVerification);
    }

    public function testCannotSendWithoutBrand(): void
    {
        $this->expectException(Client\Exception\Request::class);
        $this->expectExceptionMessage('Invalid params: The value of one or more parameters is invalid. See https://www.developer.vonage.com/api-errors#invalid-params for more information');

        $payload = [
            'to' => '07785254785',
            'client_ref' => 'my-verification',
            'brand' => '',
        ];

        $voiceRequest = new VoiceRequest($payload['to'], $payload['brand']);
        $voiceRequest->setClientRef($payload['client_ref']);

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertEquals('POST', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('verify-request-invalid', 422));

        $result = $this->verify2Client->startVerification($voiceRequest);
    }

    public function testCanHandleThrottle(): void
    {
        $this->expectException(Client\Exception\Request::class);
        $this->expectExceptionMessage('Rate Limit Hit: Please wait, then retry your request. See https://www.developer.vonage.com/api-errors#throttled for more information');

        $payload = [
            'to' => '07785254785',
            'client_ref' => 'my-verification',
            'brand' => 'my-brand',
        ];

        $voiceRequest = new VoiceRequest($payload['to'], $payload['brand']);
        $voiceRequest->setClientRef($payload['client_ref']);

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertEquals('POST', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('verify-request-throttle', 429));

        $result = $this->verify2Client->startVerification($voiceRequest);
    }

    public function testCheckValidIdAndPIN(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestJsonBodyContains('code', '24525', $request);
            $this->assertEquals('POST', $request->getMethod());
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v2/verify/c11236f4-00bf-4b89-84ba-88b25df97315',
                $uriString
            );
            return true;
        }))->willReturn($this->getResponse('verify-check-success'));

        $result = $this->verify2Client->check('c11236f4-00bf-4b89-84ba-88b25df97315', '24525');
        $this->assertTrue($result);
    }

    public function testCheckHandlesInvalidPIN(): void
    {
        $this->expectException(Client\Exception\Request::class);
        $this->expectExceptionMessage('Invalid Code: The code you provided does not match the expected value.. See https://www.developer.vonage.com/api-errors/verify#invalid-code for more information');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestJsonBodyContains('code', '24525', $request);
            $this->assertEquals('POST', $request->getMethod());
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v2/verify/c11236f4-00bf-4b89-84ba-88b25df97315',
                $uriString
            );
            return true;
        }))->willReturn($this->getResponse('verify-check-invalid', 400));

        $result = $this->verify2Client->check('c11236f4-00bf-4b89-84ba-88b25df97315', '24525');
    }

    public function testCheckHandlesInvalidRequestId(): void
    {
        $this->expectException(Client\Exception\Request::class);
        $this->expectExceptionMessage('Not Found: Request c11236f4-00bf-4b89-84ba-88b25df97315 was not found or it has been verified already.. See https://developer.vonage.com/api-errors#not-found for more information');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestJsonBodyContains('code', '24525', $request);
            $this->assertEquals('POST', $request->getMethod());
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v2/verify/c11236f4-00bf-4b89-84ba-88b25df97315',
                $uriString
            );
            return true;
        }))->willReturn($this->getResponse('verify-check-not-found', 404));

        $result = $this->verify2Client->check('c11236f4-00bf-4b89-84ba-88b25df97315', '24525');
    }

    public function testCheckHandlesConflict(): void
    {
        $this->expectException(Client\Exception\Request::class);
        $this->expectExceptionMessage('Conflict: The current Verify workflow step does not support a code.');

        $payload = [
            'to' => '07784587411',
            'brand' => 'my-brand',
        ];

        $silentAuthRequest = new SilentAuthRequest($payload['to'], $payload['brand']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn(
            $this->getResponse('verify-request-success', 202),
            $this->getResponse('verify-check-conflict', 409)
        );

        $result = $this->verify2Client->startVerification($silentAuthRequest);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('request_id', $result);

        $result = $this->verify2Client->check('c11236f4-00bf-4b89-84ba-88b25df97315', 'silent-auth-key?');
    }

    public function testCheckHandlesLockedCodeSubmission(): void
    {
        $this->expectException(Client\Exception\Request::class);
        $this->expectExceptionMessage('Invalid Code: An incorrect code has been provided too many times. Workflow terminated.. See https://www.developer.vonage.com/api-errors/verify#expired for more information');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            return true;
        }))->willReturn($this->getResponse('verify-check-locked', 410));

        $result = $this->verify2Client->check('c11236f4-00bf-4b89-84ba-88b25df97315', '24525');
    }

    public function testCheckHandlesThrottle(): void
    {
        $this->expectException(Client\Exception\Request::class);
        $this->expectExceptionMessage('Rate Limit Hit: Please wait, then retry your request. See https://www.developer.vonage.com/api-errors#throttled for more information');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            return true;
        }))->willReturn($this->getResponse('verify-check-throttle', 429));

        $result = $this->verify2Client->check('c11236f4-00bf-4b89-84ba-88b25df97315', '24525');
    }

    public function testWillCancelVerification(): void
    {
        $requestId = 'c11236f4-00bf-4b89-84ba-88b25df97315';

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v2/verify/c11236f4-00bf-4b89-84ba-88b25df97315',
                $uriString
            );

            $this->assertEquals('DELETE', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('verify-cancel-success', 204));

        $result = $this->verify2Client->cancelRequest($requestId);

        $this->assertTrue($result);
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
        return [
            [2, false],
            [3, false],
            [4, true],
            [5, true],
            [6, true],
            [7, true],
            [8, true],
            [9, true],
            [10, true],
            [11, false],
            [01, false]
        ];
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
