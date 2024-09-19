<?php

declare(strict_types=1);

namespace VonageTest\Verify2;

use Laminas\Diactoros\Request;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Verify2\Client as Verify2Client;
use Vonage\Verify2\Filters\TemplateFilter;
use Vonage\Verify2\Request\CreateCustomTemplateFragmentRequest;
use Vonage\Verify2\Request\EmailRequest;
use Vonage\Verify2\Request\SilentAuthRequest;
use Vonage\Verify2\Request\SMSRequest;
use Vonage\Verify2\Request\UpdateCustomTemplateRequest;
use Vonage\Verify2\Request\VoiceRequest;
use Vonage\Verify2\Request\WhatsAppInteractiveRequest;
use Vonage\Verify2\Request\WhatsAppRequest;
use Vonage\Verify2\VerifyObjects\Template;
use Vonage\Verify2\VerifyObjects\TemplateFragment;
use Vonage\Verify2\VerifyObjects\VerificationWorkflow;
use VonageTest\Traits\HTTPTestTrait;
use VonageTest\Traits\Psr7AssertionTrait;
use VonageTest\VonageTestCase;

class ClientTest extends VonageTestCase
{
    use Psr7AssertionTrait;
    use HTTPTestTrait;

    protected ObjectProphecy $vonageClient;
    protected Verify2Client $verify2Client;
    protected APIResource $api;
    private int $requestCount;

    public function setUp(): void
    {
        $this->responsesDirectory = __DIR__ . '/Fixtures/Responses';

        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getRestUrl()->willReturn('https://api.nexmo.com');
        $this->vonageClient->getCredentials()->willReturn(
            new Client\Credentials\Container(
                new Client\Credentials\Basic('abc', 'def'),
            )
        );

        /** @noinspection PhpParamsInspection */
        $this->api = (new APIResource())
            ->setIsHAL(true)
            ->setErrorsOn200(false)
            ->setClient($this->vonageClient->reveal())
            ->setAuthHandlers([new Client\Credentials\Handler\BasicHandler(), new Client\Credentials\Handler\KeypairHandler()])
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

    public function testCanRequestSmsWithCustomTemplate(): void
    {
        $payload = [
            'to' => '07785254785',
            'client_ref' => 'my-verification',
            'brand' => 'my-brand',
            'from' => 'vonage'
        ];

        $smsVerification = new SMSRequest($payload['to'], $payload['brand'], null, $payload['from']);
        $smsVerification->setTemplateId('33945c03-71c6-4aaf-954d-750a9b480def');

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v2/verify',
                $uriString
            );

            $this->assertRequestJsonBodyContains('template_id', '33945c03-71c6-4aaf-954d-750a9b480def', $request);
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

        $message = 'You have request a PIN which is ${code}. ${customer-name}';

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
            'from' => '07785254785',
            'client_ref' => 'my-verification',
            'brand' => 'my-brand',
        ];

        $whatsAppVerification = new WhatsAppRequest($payload['to'], $payload['brand'], $payload['from']);
        $whatsAppVerification->setClientRef($payload['client_ref']);

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('locale', 'en-us', $request);
            $this->assertRequestJsonBodyContains('channel_timeout', 300, $request);
            $this->assertRequestJsonBodyContains('client_ref', $payload['client_ref'], $request);
            $this->assertRequestJsonBodyContains('code_length', 4, $request);
            $this->assertRequestJsonBodyContains('brand', $payload['brand'], $request);
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request, true);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request, true);
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
        $this->expectException(\InvalidArgumentException::class);

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

        $this->vonageClient->send(Argument::that(fn(Request $request) => true))->willReturn($this->getResponse('verify-check-locked', 410));

        $result = $this->verify2Client->check('c11236f4-00bf-4b89-84ba-88b25df97315', '24525');
    }

    public function testCheckHandlesThrottle(): void
    {
        $this->expectException(Client\Exception\Request::class);
        $this->expectExceptionMessage('Rate Limit Hit: Please wait, then retry your request. See https://www.developer.vonage.com/api-errors#throttled for more information');

        $this->vonageClient->send(Argument::that(fn(Request $request) => true))->willReturn($this->getResponse('verify-check-throttle', 429));

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

    public function testWillHandleNextWorkflow(): void
    {
        $requestId = 'c11236f4-00bf-4b89-84ba-88b25df97315';

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v2/verify/c11236f4-00bf-4b89-84ba-88b25df97315/next_workflow',
                $uriString
            );

            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('verify-next-workflow-success'));

        $result = $this->verify2Client->nextWorkflow($requestId);

        $this->assertTrue($result);
    }

    public function testWillListCustomTemplates(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v2/verify/templates',
                $uriString
            );

            $this->assertEquals('GET', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('get-templates-success'));

        $response = $this->verify2Client->listCustomTemplates();

        foreach ($response as $template) {
            $this->assertInstanceOf(Template::class, $template);
        }
    }

    public function testWillListCustomTemplatesWithQuery(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v2/verify/templates?page=1&page_size=5',
                $uriString
            );

            $this->assertEquals('GET', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('get-templates-success'));

        $filter = new TemplateFilter();
        $filter->setPageSize(5);
        $filter->setPage(2);

        $response = $this->verify2Client->listCustomTemplates($filter);

        foreach ($response as $template) {
            $this->assertInstanceOf(Template::class, $template);
        }
    }

    public function testWillCreateTemplate(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v2/verify/templates',
                $uriString
            );

            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestJsonBodyContains('name', 'my-template', $request);

            return true;
        }))->willReturn($this->getResponse('create-template-success'));

        $template = $this->verify2Client->createCustomTemplate('my-template');
        $this->assertInstanceOf(Template::class, $template);
    }

    public function testWillGetTemplate(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v2/verify/templates/8f35a1a7-eb2f-4552-8fdf-fffdaee41bc9',
                $uriString
            );

            $this->assertEquals('GET', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('get-template-success'));

        $template = $this->verify2Client->getCustomTemplate('8f35a1a7-eb2f-4552-8fdf-fffdaee41bc9');
        $this->assertEquals('8f35a1a7-eb2f-4552-8fdf-fffdaee41bc9', $template->template_id);
    }

    public function testWillDeleteTemplate(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v2/verify/templates/8f35a1a7-eb2f-4552-8fdf-fffdaee41bc9',
                $uriString
            );

            $this->assertEquals('DELETE', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('delete-template-success', 204));

        $response = $this->verify2Client->deleteCustomTemplate('8f35a1a7-eb2f-4552-8fdf-fffdaee41bc9');
        $this->assertTrue($response);
    }

    public function testWillUpdateTemplate(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v2/verify/templates/8f35a1a7-eb2f-4552-8fdf-fffdaee41bc9',
                $uriString
            );

            $this->assertEquals('PATCH', $request->getMethod());
            $this->assertRequestJsonBodyContains('name', 'new-name', $request);
            $this->assertRequestJsonBodyContains('is_default', true, $request);

            return true;
        }))->willReturn($this->getResponse('update-template-success'));

        $updateTemplateRequest = new UpdateCustomTemplateRequest(
            'new-name',
            true
        );

        $this->verify2Client->updateCustomTemplate('8f35a1a7-eb2f-4552-8fdf-fffdaee41bc9', $updateTemplateRequest);
    }

    public function testWillListTemplateFragments(): void
    {
        $this->requestCount = 0;
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->requestCount++;
            $uri = $request->getUri();
            $uriString = $uri->__toString();

            if ($this->requestCount == 1) {
                $this->assertEquals(
                    'https://api.nexmo.com/v2/verify/templates/8f35a1a7-eb2f-4552-8fdf-fffdaee41bc9/template_fragments',
                    $uriString
                );
            }

            if ($this->requestCount == 2) {
                $this->assertEquals(
                    'https://api.nexmo.com/v2/verify/templates/c70f446e-997a-4313-a081-60a02a31dc19/template_fragments?page=3',
                    $uriString
                );
            }

            $this->assertEquals('GET', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('list-template-fragment-success'), $this->getResponse('list-template-fragment-success-2'));

        $fragments = $this->verify2Client->listTemplateFragments('8f35a1a7-eb2f-4552-8fdf-fffdaee41bc9');

        foreach ($fragments as $fragment) {
            $this->assertInstanceOf(TemplateFragment::class, $fragment);
        }

        $this->requestCount = 0;
    }

    public function testWillListTemplateFragmentsWithQuery(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v2/verify/templates/8f35a1a7-eb2f-4552-8fdf-fffdaee41bc9/template_fragments?page=2&page_size=10',
                $uriString
            );

            $this->assertEquals('GET', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('list-template-fragment-success'));
        $templateFilter = new TemplateFilter();
        $templateFilter->setPage(2);
        $templateFilter->setPageSize(10);

        $fragments = $this->verify2Client->listTemplateFragments('8f35a1a7-eb2f-4552-8fdf-fffdaee41bc9', $templateFilter);

        foreach ($fragments as $fragment) {
            $this->assertInstanceOf(TemplateFragment::class, $fragment);
        }
    }

    public function testWillCreateTemplateFragment(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v2/verify/templates/c70f446e-997a-4313-a081-60a02a31dc19/template_fragments',
                $uriString
            );
            $this->assertRequestJsonBodyContains('channel', 'sms', $request);
            $this->assertRequestJsonBodyContains('locale', 'en-us', $request);
            $this->assertRequestJsonBodyContains('text', 'The authentication code for your ${brand} is: ${code}', $request);

            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('create-template-fragment-success'));

        $createTemplateFragmentRequest = new CreateCustomTemplateFragmentRequest(
            "sms",
            "en-us",
            'The authentication code for your ${brand} is: ${code}'
        );
        
        $template = $this->verify2Client->createCustomTemplateFragment('c70f446e-997a-4313-a081-60a02a31dc19', $createTemplateFragmentRequest);

        $this->assertInstanceOf(TemplateFragment::class, $template);
    }

    public function testWillGetTemplateFragment(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v2/verify/templates/8f35a1a7-eb2f-4552-8fdf-fffdaee41bc9/template_fragments/c70f446e-997a-4313-a081-60a02a31dc19',
                $uriString
            );

            $this->assertEquals('GET', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('get-template-fragment-success'));

        $fragment = $this->verify2Client->getCustomTemplateFragment('8f35a1a7-eb2f-4552-8fdf-fffdaee41bc9', 'c70f446e-997a-4313-a081-60a02a31dc19');
    }

    public function testWillUpdateTemplateFragment(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v2/verify/templates/8f35a1a7-eb2f-4552-8fdf-fffdaee41bc9/template_fragments/c70f446e-997a-4313-a081-60a02a31dc19',
                $uriString
            );

            $this->assertEquals('PATCH', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('update-template-fragment-success'));

        $fragment = $this->verify2Client->updateCustomTemplateFragment('8f35a1a7-eb2f-4552-8fdf-fffdaee41bc9', 'c70f446e-997a-4313-a081-60a02a31dc19', 'The authentication code for your ${brand} is: ${code}');
        $this->assertInstanceOf(TemplateFragment::class, $fragment);
    }

    public function testWillDeleteTemplateFragment(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v2/verify/templates/8f35a1a7-eb2f-4552-8fdf-fffdaee41bc9/template_fragments/c70f446e-997a-4313-a081-60a02a31dc19',
                $uriString
            );

            $this->assertEquals('DELETE', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('delete-template-fragment-success'));

        $response = $this->verify2Client->deleteCustomTemplateFragment('8f35a1a7-eb2f-4552-8fdf-fffdaee41bc9', 'c70f446e-997a-4313-a081-60a02a31dc19');
        $this->assertTrue($response);
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
            [13, false],
            [564, true],
            [921, false],
        ];
    }
//
//    public function testIntegration()
//    {
//        $credentials = new Client\Credentials\Keypair(
//            file_get_contents('/Users/JSeconde/Sites/vonage-php-sdk-core/test/Verify2/private.key'),
//            '4a875f7e-2559-4fb5-84f6-f8b144f6e9f6'
//        );
//
//        $liveClient = new Client($credentials);
//
//        $response = $liveClient->verify2()->createCustomTemplate('example-template');

//        $smsRequest = new SMSRequest('447738066610', 'VONAGE', null, '447738066610');
//        $response = $liveClient->verify2()->startVerification($smsRequest);
//        var_dump($response);
//    }
}
