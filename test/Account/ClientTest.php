<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\Test\Account;

use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Account\Client as AccountClient;
use Vonage\Account\PrefixPrice;
use Vonage\Client;
use Vonage\Client\Exception;
use Vonage\Client\Exception\Request;
use Vonage\Client\Exception\Server;
use Vonage\Client\Exception\Validation;
use Vonage\InvalidResponseException;
use Vonage\Network;
use Vonage\Test\Psr7AssertionTrait;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    /**
     * @var mixed
     */
    protected $vonageClient;

    /**
     * @var AccountClient
     */
    protected $accountClient;

    /**
     * APIResource
     */
    protected $api;

    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getRestUrl()->willReturn('https://rest.nexmo.com');
        $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');

        $this->accountClient = new AccountClient();
        /** @noinspection PhpParamsInspection */
        $this->accountClient->setClient($this->vonageClient->reveal());
    }

    /**
     * @throws Exception\Exception
     * @throws ClientExceptionInterface
     */
    public function testTopUp(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/top-up', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestFormBodyContains('trx', 'ABC123', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('empty'));

        $this->accountClient->topUp('ABC123');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     */
    public function testTopUpFailsWith4xx(): void
    {
        $this->expectException(Request::class);
        $this->expectExceptionMessage('authentication failed');

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/top-up', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestFormBodyContains('trx', 'ABC123', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('auth-failure', 401));

        $this->accountClient->topUp('ABC123');
    }

    /**
     * Handle when a proper error is returned from the top-up API
     * While this client library is building the response correctly, we need to
     * simulate a non-200 response
     *
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     */
    public function testTopUpFailsDueToBadRequest(): void
    {
        $this->expectException(Request::class);
        $this->expectExceptionMessage('Bad Request');

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/top-up', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestFormBodyContains('trx', 'ABC123', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('top-up-bad-request', 400));

        $this->accountClient->topUp('ABC123');
    }

    /**
     * Handle when a proper error is returned from the top-up API
     * While this client library is building the response correctly, we need to
     * simulate a non-200 response
     *
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     */
    public function testTopUpFailsDueToBadRequestReturns500(): void
    {
        $this->expectException(Server::class);
        $this->expectExceptionMessage('Bad Request');

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/top-up', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestFormBodyContains('trx', 'ABC123', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('top-up-bad-request', 500));

        $this->accountClient->topUp('ABC123');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Server
     */
    public function testGetBalance(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-balance', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('get-balance'));

        $this->accountClient->getBalance();
    }

    /**
     * Handle if the balance API returns a completely empty body
     * Not sure how this would happen in real life, but making sure we work
     *
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Server
     * @author Chris Tankersley <chris.tankersley@vonage.com>
     *
     */
    public function testGetBalanceWithNoResults(): void
    {
        $this->expectException(Server::class);
        $this->expectExceptionMessage('No results found');

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-balance', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('empty'));

        $this->accountClient->getBalance();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Server
     */
    public function testGetConfig(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/settings', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('get-config'));

        $this->accountClient->getConfig();
    }

    /**
     * Handle if the balance API returns a completely empty body
     * Not sure how this would happen in real life, but making sure we work
     *
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Server
     * @author Chris Tankersley <chris.tankersley@vonage.com>
     *
     */
    public function testGetConfigBlankResponse(): void
    {
        $this->expectException(Server::class);
        $this->expectExceptionMessage('Response was empty');

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/settings', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('empty'));

        $this->accountClient->getConfig();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Server
     */
    public function testUpdateConfig(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/settings', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestFormBodyContains('moCallBackUrl', 'https://example.com/other', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('get-config'));

        $this->accountClient->updateConfig([
            "sms_callback_url" => "https://example.com/other",
            "dr_callback_url" => "https://example.com/receipt",
        ]);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Server
     */
    public function testUpdateConfigThrowsNon200(): void
    {
        $this->expectException(Request::class);
        $this->expectExceptionMessage('authentication failed');

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/settings', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestFormBodyContains('moCallBackUrl', 'https://example.com/other', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('auth-failure', 401));

        $this->accountClient->updateConfig(["sms_callback_url" => "https://example.com/other"]);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Server
     */
    public function testUpdateConfigReturnsBlankResponse(): void
    {
        $this->expectException(Server::class);
        $this->expectExceptionMessage('Response was empty');

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/settings', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestFormBodyContains('moCallBackUrl', 'https://example.com/other', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('empty', 200));

        $this->accountClient->updateConfig(["sms_callback_url" => "https://example.com/other"]);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Request
     * @throws Server
     */
    public function testGetSmsPricing(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-pricing/outbound/sms', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            $this->assertRequestQueryContains('country', 'US', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('smsprice-us'));

        $smsPrice = $this->accountClient->getSmsPrice('US');

        self::assertInstanceOf(Network::class, @$smsPrice['networks']['311310']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Request
     * @throws Server
     */
    public function testGetSmsPricingReturnsEmptySet(): void
    {
        $this->expectException(Server::class);
        $this->expectExceptionMessage('No results found');

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-pricing/outbound/sms', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            $this->assertRequestQueryContains('country', 'XX', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('empty'));

        $this->accountClient->getSmsPrice('XX');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Request
     * @throws Server
     */
    public function testGetVoicePricing(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-pricing/outbound/voice', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            $this->assertRequestQueryContains('country', 'US', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('voiceprice-us'));

        $voicePrice = $this->accountClient->getVoicePrice('US');

        self::assertInstanceOf(Network::class, @$voicePrice['networks']['311310']);
    }

    public function testGetPrefixPricing(): void
    {
        $first = $this->getResponse('prefix-pricing');
        $noResults = $this->getResponse('prefix-pricing-no-results');

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            static $hasRun = false;

            $this->assertEquals('/account/get-prefix-pricing/outbound', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            $this->assertRequestQueryContains('prefix', '263', $request);

            if ($hasRun) {
                $this->assertRequestQueryContains('page_index', '2', $request);
            }

            $hasRun = true;
            return true;
        }))->shouldBeCalledTimes(2)->willReturn($first, $noResults);

        $prefixPrice = $this->accountClient->getPrefixPricing('263');
        self::assertInstanceOf(PrefixPrice::class, @$prefixPrice[0]);
        self::assertInstanceOf(Network::class, @$prefixPrice[0]['networks']['64804']);
    }

    public function testGetPrefixPricingNoResults(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-prefix-pricing/outbound', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            $this->assertRequestQueryContains('prefix', '263', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('prefix-pricing-no-results'));

        $prefixPrice = $this->accountClient->getPrefixPricing('263');
        self::assertEmpty($prefixPrice);
    }

    public function testGetPrefixPricingGenerates4xxError(): void
    {
        $this->expectException(Request::class);
        $this->expectExceptionMessage('authentication failed');

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-prefix-pricing/outbound', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            $this->assertRequestQueryContains('prefix', '263', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('auth-failure', 401));

        $this->accountClient->getPrefixPricing('263');
    }

    public function testGetPrefixPricingGenerates5xxError(): void
    {
        $this->expectException(Server::class);
        $this->expectExceptionMessage('unknown error');

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-prefix-pricing/outbound', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            $this->assertRequestQueryContains('prefix', '263', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('prefix-pricing-server-failure', 500));

        $this->accountClient->getPrefixPricing('263');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws InvalidResponseException
     */
    public function testListSecrets(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/accounts/abcd1234/secrets', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('secret-management/list'));

        $this->accountClient->listSecrets('abcd1234');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws InvalidResponseException
     */
    public function testListSecretsServerError(): void
    {
        $this->expectException(Exception\Server::class);

        $this->vonageClient->send(
            Argument::any()
        )->willReturn($this->getGenericResponse('500', 500));

        $this->accountClient->listSecrets('abcd1234');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws InvalidResponseException
     */
    public function testListSecretsRequestError(): void
    {
        $this->expectException(Exception\Request::class);

        $this->vonageClient->send(
            Argument::any()
        )->willReturn($this->getGenericResponse('401', 401));

        $this->accountClient->listSecrets('abcd1234');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws InvalidResponseException
     */
    public function testGetSecret(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals(
                '/accounts/abcd1234/secrets/ad6dc56f-07b5-46e1-a527-85530e625800',
                $request->getUri()->getPath()
            );
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('secret-management/get'));

        $this->accountClient->getSecret('abcd1234', 'ad6dc56f-07b5-46e1-a527-85530e625800');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws InvalidResponseException
     */
    public function testGetSecretsServerError(): void
    {
        $this->expectException(Exception\Server::class);

        $this->vonageClient->send(
            Argument::any()
        )->willReturn($this->getGenericResponse('500', 500));

        $this->accountClient->getSecret('abcd1234', 'ad6dc56f-07b5-46e1-a527-85530e625800');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws InvalidResponseException
     */
    public function testGetSecretsRequestError(): void
    {
        $this->expectException(Exception\Request::class);

        $this->vonageClient->send(
            Argument::any()
        )->willReturn($this->getGenericResponse('401', 401));

        $this->accountClient->getSecret('abcd1234', 'ad6dc56f-07b5-46e1-a527-85530e625800');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws InvalidResponseException
     * @throws Request
     * @throws Validation
     */
    public function testCreateSecret(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/accounts/abcd1234/secrets', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('secret-management/create'));

        $this->accountClient->createSecret('abcd1234', 'example-4PI-secret');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws InvalidResponseException
     * @throws Request
     * @throws Validation
     */
    public function testCreateSecretsServerError(): void
    {
        $this->expectException(Exception\Server::class);

        $this->vonageClient->send(
            Argument::any()
        )->willReturn($this->getGenericResponse('500', 500));

        $this->accountClient->createSecret('abcd1234', 'example-4PI-secret');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws InvalidResponseException
     * @throws Request
     * @throws Validation
     */
    public function testCreateSecretsRequestError(): void
    {
        $this->expectException(Exception\Request::class);

        $this->vonageClient->send(Argument::any())->willReturn($this->getGenericResponse('401', 401));

        $this->accountClient->createSecret('abcd1234', 'example-4PI-secret');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws InvalidResponseException
     * @throws Request
     */
    public function testCreateSecretsValidationError(): void
    {
        try {
            $this->vonageClient->send(Argument::any())
                ->willReturn($this->getResponse('secret-management/create-validation', 400));
            $this->accountClient->createSecret('abcd1234', 'example-4PI-secret');
        } catch (Validation $e) {
            self::assertEquals(
                'Bad Request: The request failed due to validation errors. ' .
                'See https://developer.nexmo.com/api-errors/account/secret-management#validation ' .
                'for more information',
                $e->getMessage()
            );
            self::assertEquals(
                [
                    [
                        'name' => 'secret',
                        'reason' => 'Does not meet complexity requirements'
                    ]
                ],
                $e->getValidationErrors()
            );
        }
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     */
    public function testDeleteSecret(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals(
                '/accounts/abcd1234/secrets/ad6dc56f-07b5-46e1-a527-85530e625800',
                $request->getUri()->getPath()
            );
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('DELETE', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('secret-management/delete'));

        $this->accountClient->deleteSecret('abcd1234', 'ad6dc56f-07b5-46e1-a527-85530e625800');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     */
    public function testDeleteSecretsServerError(): void
    {
        $this->expectException(Exception\Server::class);
        $this->vonageClient->send(Argument::any())->willReturn($this->getGenericResponse('500', 500));
        $this->accountClient->deleteSecret('abcd1234', 'ad6dc56f-07b5-46e1-a527-85530e625800');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     */
    public function testDeleteSecretsRequestError(): void
    {
        $this->expectException(Exception\Request::class);
        $this->vonageClient->send(Argument::any())->willReturn($this->getGenericResponse('401', 401));
        $this->accountClient->deleteSecret('abcd1234', 'ad6dc56f-07b5-46e1-a527-85530e625800');
    }

    /**
     * Get the API response we'd expect for a call to the API.
     *
     * @param string $type
     * @param int $status
     * @return Response
     */
    protected function getResponse(string $type = 'success', int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
    }

    /**
     * @param string $type
     * @param int $status
     * @return Response
     */
    protected function getGenericResponse(string $type = 'success', int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/../responses/general/' . $type . '.json', 'rb'), $status);
    }
}
