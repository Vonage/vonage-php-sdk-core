<?php

declare(strict_types=1);

namespace VonageTest\NumberVerification;

use Laminas\Diactoros\Request;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\NumberVerification\Client as NumberVerificationClient;
use VonageTest\Traits\HTTPTestTrait;
use VonageTest\VonageTestCase;

class ClientTest extends VonageTestCase
{
    use HTTPTestTrait;

    protected ObjectProphecy $vonageClient;
    protected NumberVerificationClient $numberVerificationClient;
    protected APIResource $api;
    protected Client|ObjectProphecy $handlerClient;
    protected int $requestCount = 0;

    public function setUp(): void
    {
        $this->responsesDirectory = __DIR__ . '/Fixtures/Responses';

        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getCredentials()->willReturn(
            new Client\Credentials\Container(new Client\Credentials\Gnp(
                '447700900000',
                file_get_contents(__DIR__ . '/../Client/Credentials/test.key'),
                'def',
            ))
        );

        $reveal = $this->vonageClient->reveal();

        $handler = new Client\Credentials\Handler\NumberVerificationGnpHandler();
        $handler->setClient($reveal);

        $this->api = (new APIResource())
            ->setClient($reveal)
            ->setAuthHandlers($handler)
            ->setBaseUrl('https://api-eu.vonage.com/camara/number-verification/v031/');

        $this->numberVerificationClient = new NumberVerificationClient($this->api);
    }

    public function testHasSetupClientCorrectly(): void
    {
        $this->assertInstanceOf(NumberVerificationClient::class, $this->numberVerificationClient);
    }

    public function testWillCreateUrl()
    {
        $frontendUrl = $this->numberVerificationClient->buildFrontEndUrl(
            '0773806641999',
            'https://myapp.com/verify',
            'open',
        );

        $this->assertEquals('https://oidc.idp.vonage.com/oauth2/authclient_id=def&redirect_uri=https%3A%2F%2Fmyapp.com%2Fverify&state=open&scope=openid+dpv%3AFraudPreventionAndDetection%23number-verification-verify-read&response_type=code&login_hint=0773806641999', $frontendUrl);
    }

    public function testWillCompleteVerification()
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->requestCount++;

            if ($this->requestCount == 1) {
                $uri = $request->getUri();
                $uriString = $uri->__toString();
                $this->assertEquals(
                    'https://api-eu.vonage.com/oauth2/token',
                    $uriString
                );

                $headers = $request->getHeaders();
                $this->assertArrayHasKey('Authorization', $headers);
                $this->assertEquals('application/x-www-form-urlencoded', $headers['content-type'][0]);
                $this->assertRequestFormBodyContains('grant_type', 'authorization_code', $request);
                $this->assertRequestFormBodyContains('code', '12345', $request);

                return true;
            }

            if ($this->requestCount == 2) {
                $uri = $request->getUri();
                $uriString = $uri->__toString();
                $this->assertEquals(
                    'https://api-eu.vonage.com/camara/number-verification/v031/verify',
                    $uriString
                );

                $headers = $request->getHeaders();
                $this->assertArrayHasKey('Authorization', $headers);

                $this->assertRequestJsonBodyContains('phoneNumber', '0773806641999', $request);

                return true;
            }
        }))->willReturn(
            $this->getResponse('ni-token-success'),
            $this->getResponse('ni-check-success')
        );

        $result = $this->numberVerificationClient->verifyNumber(
            '0773806641999',
            '12345',
            'open'
        );

        $this->assertTrue($result);
        $this->requestCount = 0;
    }

    public function testWillFailVerification()
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->requestCount++;

            if ($this->requestCount == 1) {
                $uri = $request->getUri();
                $uriString = $uri->__toString();
                $this->assertEquals(
                    'https://api-eu.vonage.com/oauth2/token',
                    $uriString
                );
                return true;
            }

            if ($this->requestCount == 2) {
                $uri = $request->getUri();
                $uriString = $uri->__toString();
                $this->assertEquals(
                    'https://api-eu.vonage.com/camara/number-verification/v031/verify',
                    $uriString
                );

                return true;
            }
        }))->willReturn(
            $this->getResponse('ni-token-success'),
            $this->getResponse('ni-check-failed')
        );

        $result = $this->numberVerificationClient->verifyNumber(
            '0773806641999',
            '12345',
            'open'
        );

        $this->assertFalse($result);
    }
}
