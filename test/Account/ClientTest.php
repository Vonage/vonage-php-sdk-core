<?php

declare(strict_types=1);

namespace VonageTest\Account;

use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Account\Client as AccountClient;
use Vonage\Account\Network;
use Vonage\Account\PrefixPrice;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Exception as ClientException;
use Vonage\Client\Exception\Request as RequestException;
use Vonage\Client\Exception\Server as ServerException;
use VonageTest\Traits\HTTPTestTrait;
use VonageTest\Traits\Psr7AssertionTrait;
use VonageTest\VonageTestCase;

class ClientTest extends VonageTestCase
{
    use Psr7AssertionTrait;
    use HTTPTestTrait;

    protected AccountClient $accountClient;

    protected APIResource $api;

    public function setUp(): void
    {
        $this->responsesDirectory = __DIR__ . '/responses';

        $this->api = new APIResource(new Client\VonageConfig());
        $this->api->setBaseUrl('https://rest.nexmo.com')
            ->setIsHAL(false)
            ->setAuthHandlers(new Client\Credentials\Handler\BasicQueryHandler())
            ->setBaseUri('/account')
            ->setCredentials(new Client\Credentials\Container(new Client\Credentials\Basic('abc', 'def')));

        $this->api->setClient($this->vonageClient->reveal());
        $this->accountClient = new AccountClient($this->api);
    }

    /**
     * @throws ClientException\Exception
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
     * @throws ClientException\Exception
     */
    public function testTopUpFailsWith4xx(): void
    {
        $this->expectException(RequestException::class);
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
     * @throws ClientException\Exception
     */
    public function testTopUpFailsDueToBadRequest(): void
    {
        $this->expectException(RequestException::class);
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
     * @throws ClientException\Exception
     */
    public function testTopUpFailsDueToBadRequestReturns500(): void
    {
        $this->expectException(ServerException::class);
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
     * @throws ClientException\Exception
     * @throws ServerException
     */
    public function testGetBalance(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-balance', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertRequestMethod('GET', $request);

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals('https://rest.nexmo.com/account/get-balance?api_key=abc&api_secret=def', $uriString);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('get-balance'));

        $this->accountClient->getBalance();
    }

    /**
     * Handle if the balance API returns a completely empty body
     * Not sure how this would happen in real life, but making sure we work
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ServerException
     *
     * @author Chris Tankersley <chris.tankersley@vonage.com>
     */
    public function testGetBalanceWithNoResults(): void
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('No results found');

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-balance', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertRequestMethod('GET', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('empty'));

        $this->accountClient->getBalance();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ServerException
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
     * @throws ClientException\Exception
     * @throws ServerException
     *
     * @author Chris Tankersley <chris.tankersley@vonage.com>
     */
    public function testGetConfigBlankResponse(): void
    {
        $this->expectException(ServerException::class);
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
     * @throws ClientException\Exception
     * @throws ServerException
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
     * @throws ClientException\Exception
     * @throws ServerException
     */
    public function testUpdateConfigThrowsNon200(): void
    {
        $this->expectException(RequestException::class);
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
     * @throws ClientException\Exception
     * @throws ServerException
     */
    public function testUpdateConfigReturnsBlankResponse(): void
    {
        $this->expectException(ServerException::class);
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
     * @throws ClientException\Exception
     * @throws RequestException
     * @throws ServerException
     */
    public function testGetSmsPricing(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-pricing/outbound/sms', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertRequestMethod('GET', $request);
            $this->assertRequestQueryContains('country', 'US', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('smsprice-us'));

        $smsPrice = $this->accountClient->getSmsPrice('US');
        $networks = $smsPrice->getNetworks();
        $this->assertInstanceOf(Network::class, $networks['311310']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws RequestException
     * @throws ServerException
     */
    public function testGetSmsPricingReturnsEmptySet(): void
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('No results found');

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-pricing/outbound/sms', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertRequestMethod('GET', $request);
            $this->assertRequestQueryContains('country', 'XX', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('empty'));

        $this->accountClient->getSmsPrice('XX');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws RequestException
     * @throws ServerException
     */
    public function testGetVoicePricing(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-pricing/outbound/voice', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertRequestMethod('GET', $request);
            $this->assertRequestQueryContains('country', 'US', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('voiceprice-us'));

        $voicePrice = $this->accountClient->getVoicePrice('US');
        $networks = $voicePrice->getNetworks();
        $this->assertInstanceOf(Network::class, $networks['311310']);
    }

    public function testGetPrefixPricing(): void
    {
        $first = $this->getResponse('prefix-pricing');
        $noResults = $this->getResponse('prefix-pricing-no-results');

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            static $hasRun = false;

            $this->assertEquals('/account/get-prefix-pricing/outbound', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertRequestMethod('GET', $request);
            $this->assertRequestQueryContains('prefix', '263', $request);

            if ($hasRun) {
                $this->assertRequestQueryContains('page_index', '2', $request);
            }

            $hasRun = true;
            return true;
        }))->shouldBeCalledTimes(2)->willReturn($first, $noResults);

        $prefixPrice = $this->accountClient->getPrefixPricing('263');
        $this->assertInstanceOf(PrefixPrice::class, $prefixPrice[0]);
        $networks = $prefixPrice[0]->getNetworks();
        $this->assertInstanceOf(Network::class, $networks['64804']);
    }

    public function testGetPrefixPricingNoResults(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-prefix-pricing/outbound', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertRequestMethod('GET', $request);
            $this->assertRequestQueryContains('prefix', '263', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('prefix-pricing-no-results'));

        $prefixPrice = $this->accountClient->getPrefixPricing('263');
        $this->assertEmpty($prefixPrice);
    }

    public function testGetPrefixPricingGenerates4xxError(): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('authentication failed');

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-prefix-pricing/outbound', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertRequestMethod('GET', $request);
            $this->assertRequestQueryContains('prefix', '263', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('auth-failure', 401));

        $this->accountClient->getPrefixPricing('263');
    }

    public function testGetPrefixPricingGenerates5xxError(): void
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('unknown error');

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-prefix-pricing/outbound', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertRequestMethod('GET', $request);
            $this->assertRequestQueryContains('prefix', '263', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('prefix-pricing-server-failure', 500));

        $this->accountClient->getPrefixPricing('263');
    }
}
