<?php

declare(strict_types=1);

namespace VonageTest\Verify2;

use Laminas\Diactoros\Request;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Vonage\Client\APIResource;
use Vonage\Messages\Channel\SMS\SMSText;
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

    }

    public function testCanRequestSMS(): void
    {
        $payload = [
            'locale' => new VerificationLocale(),
            'client_ref' => 'my-verification',
            'brand' => 'my-brand',
        ];

        $smsVerification = new SMSVerification();

        $this->vonageClient->send(Argument::that(function (Request $request) use ($payload) {
            $this->assertEquals(
                'Bearer ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 7)
            );

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));

        $result = $this->verify2Client->send($smsVerification);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('request_id', $result);
    }

    /**
     * @dataProvider LocaleProvider
     */
    public function testCannotRequestSMSWithInvalidLocale(): void
    {

    }

    /**
     * @dataProvider TimeoutProvider
     */
    public function testTimeoutParsesCorrectly(): void
    {

    }

    /**
     * @dataProvider PINLengthProvider
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
}
