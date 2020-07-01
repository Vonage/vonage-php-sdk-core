<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Account;

use Nexmo\Network;
use Prophecy\Argument;
use Nexmo\Account\Client;
use Nexmo\Account\Config;
use Nexmo\Account\Secret;
use Nexmo\Account\Balance;
use Nexmo\Account\Exception\NotFoundException;
use Nexmo\Account\SmsPrice;
use Nexmo\Client\Exception;
use Zend\Diactoros\Response;
use Nexmo\Account\VoicePrice;
use Nexmo\Client\APIResource;
use Nexmo\Account\PrefixPrice;
use Nexmo\Account\PriceFactory;
use PHPUnit\Framework\TestCase;
use NexmoTest\Psr7AssertionTrait;
use Nexmo\Account\SecretCollection;
use Nexmo\Client\Exception\Validation;
use Nexmo\Entity\Hydrator\ArrayHydrator;
use Psr\Http\Message\RequestInterface;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    protected $nexmoClient;

    /**
     * @var Client
     */
    protected $accountClient;

    /**
     * @var APIResource
     */
    protected $accountAPIClient;

    /**
     * @var ArrayHydrator
     */
    protected $hydrator;

    /**
     * @var PriceFactory
     */
    protected $priceFactory;

    /**
     * @var APIResource
     */
    protected $secretsAPIClient;

    public function setUp()
    {
        $this->nexmoClient = $this->prophesize('Nexmo\Client');
        $this->nexmoClient->getRestUrl()->willReturn('https://rest.nexmo.com');
        $this->nexmoClient->getApiUrl()->willReturn('https://api.nexmo.com');

        $this->accountAPIClient = new APIResource();
        $this->accountAPIClient
            ->setBaseUrl('https://rest.nexmo.com')
            ->setIsHAL(false)
            ->setBaseUri('/account')
            ->setCollectionName('')
            ->setClient($this->nexmoClient->reveal())
        ;

        $this->secretsAPIClient = new APIResource();
        $this->secretsAPIClient
            ->setBaseUri('/accounts')
            ->setClient($this->nexmoClient->reveal())
        ;

        $this->hydrator = new ArrayHydrator();
        $this->priceFactory = new PriceFactory($this->hydrator);

        $this->accountClient = new Client($this->accountAPIClient, $this->secretsAPIClient, $this->priceFactory);
    }

    public function testTopUp()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/top-up', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestFormBodyContains('trx', 'ABC123', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('empty'));

        $this->accountClient->topUp('ABC123');
    }

    public function testTopUpFailsWith4xx()
    {
        $this->expectException('\Nexmo\Client\Exception\Request');
        $this->expectExceptionMessage('authentication failed');

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
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
     */
    public function testTopUpFailsDueToBadRequest()
    {
        $this->expectException('\Nexmo\Client\Exception\Request');
        $this->expectExceptionMessage('Bad Request');

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
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
     */
    public function testTopUpFailsDueToBadRequestReturns500()
    {
        $this->expectException('\Nexmo\Client\Exception\Server');
        $this->expectExceptionMessage('Bad Request');

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/top-up', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestFormBodyContains('trx', 'ABC123', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('top-up-bad-request', 500));

        $this->accountClient->topUp('ABC123');
    }

    public function testGetBalance()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-balance', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('get-balance'));

        $balance = $this->accountClient->getBalance();
        $this->assertInstanceOf(Balance::class, $balance);
    }

    /**
     * Handle if the balance API returns a completely empty body
     * Not sure how this would happen in real life, but making sure we work
     *
     * @author Chris Tankersley <chris.tankersley@vonage.com>
     */
    public function testGetBalanceWithNoResults()
    {
        $this->expectException('\Nexmo\Client\Exception\Server');
        $this->expectExceptionMessage('Unable to retrieve balance');

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-balance', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('empty'));

        $balance = $this->accountClient->getBalance();
        $this->assertInstanceOf(Balance::class, $balance);
    }

    public function testGetConfig()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/settings', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('get-config'));

        $config = $this->accountClient->getConfig();
        $this->assertInstanceOf(Config::class, $config);
    }

    /**
     * Handle if the balance API returns a completely empty body
     * Not sure how this would happen in real life, but making sure we work
     *
     * @author Chris Tankersley <chris.tankersley@vonage.com>
     */
    public function testGetConfigBlankResponse()
    {
        $this->expectException('\Nexmo\Client\Exception\Server');
        $this->expectExceptionMessage('Response was empty');

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/settings', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('empty'));

        $config = $this->accountClient->getConfig();
        $this->assertInstanceOf(Config::class, $config);
    }

    public function testUpdateConfig()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/settings', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestFormBodyContains('moCallBackUrl', 'https://example.com/other', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('get-config'));

        $config = $this->accountClient->updateConfig([
            "sms_callback_url" => "https://example.com/other",
            "dr_callback_url" => "https://example.com/receipt",
        ]);
        $this->assertInstanceOf(Config::class, $config);
    }

    public function testUpdateConfigThrowsNon200()
    {
        $this->expectException('\Nexmo\Client\Exception\Request');
        $this->expectExceptionMessage('authentication failed');

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/settings', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestFormBodyContains('moCallBackUrl', 'https://example.com/other', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('auth-failure', 401));

        $config = $this->accountClient->updateConfig(["sms_callback_url" => "https://example.com/other"]);
        $this->assertInstanceOf(Config::class, $config);
    }

    public function testUpdateConfigReturnsBlankResponse()
    {
        $this->expectException('\Nexmo\Client\Exception\Server');
        $this->expectExceptionMessage('Response was empty');

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/settings', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestFormBodyContains('moCallBackUrl', 'https://example.com/other', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('empty', 200));

        $config = $this->accountClient->updateConfig(["sms_callback_url" => "https://example.com/other"]);
        $this->assertInstanceOf(Config::class, $config);
    }

    public function testGetSmsPricing()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-pricing/outbound/sms', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            $this->assertRequestQueryContains('country', 'US', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('smsprice-us'));

        $smsPrice = $this->accountClient->getSmsPrice('US');
        $this->assertInstanceOf(SmsPrice::class, $smsPrice);
        $this->assertInstanceOf(Network::class, $smsPrice->getNetworks()['311310']);
    }

    public function testGetSmsPricingReturnsEmptySet()
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('No results found');

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-pricing/outbound/sms', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            $this->assertRequestQueryContains('country', 'XX', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('empty'));

        $smsPrice = $this->accountClient->getSmsPrice('XX');
    }

    public function testGetVoicePricing()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-pricing/outbound/voice', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            $this->assertRequestQueryContains('country', 'US', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('voiceprice-us'));

        $voicePrice = $this->accountClient->getVoicePrice('US');
        $this->assertInstanceOf(VoicePrice::class, $voicePrice);
        $this->assertInstanceOf(Network::class, $voicePrice->getNetworks()['311310']);
    }

    public function testGetPrefixPricing()
    {
        $first = $this->getResponse('prefix-pricing');
        $noResults = $this->getResponse('prefix-pricing-no-results');
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
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
        $this->assertInstanceOf(PrefixPrice::class, $prefixPrice[0]);
        $this->assertInstanceOf(Network::class, $prefixPrice[0]->getNetworks()['64804']);
    }

    public function testGetPrefixPricingNoResults()
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('No results found');

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-prefix-pricing/outbound', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            $this->assertRequestQueryContains('prefix', '263', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('prefix-pricing-no-results'));

        $prefixPrice = $this->accountClient->getPrefixPricing('263');
    }

    public function testGetPrefixPricingGenerates4xxError()
    {
        $this->expectException('Nexmo\Client\Exception\Request');
        $this->expectExceptionMessage('authentication failed');

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-prefix-pricing/outbound', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            $this->assertRequestQueryContains('prefix', '263', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('auth-failure', 401));

        $prefixPrice = $this->accountClient->getPrefixPricing('263');
    }

    public function testGetPrefixPricingGenerates5xxError()
    {
        $this->expectException('Nexmo\Client\Exception\Server');
        $this->expectExceptionMessage('unknown error');

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/get-prefix-pricing/outbound', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            $this->assertRequestQueryContains('prefix', '263', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('prefix-pricing-server-failure', 500));

        $prefixPrice = $this->accountClient->getPrefixPricing('263');
    }

    public function testListSecrets()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/accounts/abcd1234/secrets', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('secret-management/list'));

        $secrets = $this->accountClient->listSecrets('abcd1234');
        $this->assertInstanceOf(SecretCollection::class, $secrets);
    }

    public function testListSecretsServerError()
    {
        $this->expectException(Exception\Server::class);
        $this->nexmoClient->send(Argument::any())->willReturn($this->getGenericResponse('500', 500));
        $this->accountClient->listSecrets('abcd1234');
    }

    public function testListSecretsRequestError()
    {
        $this->expectException(Exception\Request::class);
        $this->nexmoClient->send(Argument::any())->willReturn($this->getGenericResponse('401', 401));
        $this->accountClient->listSecrets('abcd1234');
    }

    public function testGetSecret()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/accounts/abcd1234/secrets/ad6dc56f-07b5-46e1-a527-85530e625800', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('secret-management/get'));

        $secret = $this->accountClient->getSecret('abcd1234', 'ad6dc56f-07b5-46e1-a527-85530e625800');
        $this->assertInstanceOf(Secret::class, $secret);
    }

    public function testGetSecretsServerError()
    {
        $this->expectException(Exception\Server::class);
        $this->nexmoClient->send(Argument::any())->willReturn($this->getGenericResponse('500', 500));
        $this->accountClient->getSecret('abcd1234', 'ad6dc56f-07b5-46e1-a527-85530e625800');
    }

    public function testGetSecretsRequestError()
    {
        $this->expectException(Exception\Request::class);
        $this->nexmoClient->send(Argument::any())->willReturn($this->getGenericResponse('401', 401));
        $this->accountClient->getSecret('abcd1234', 'ad6dc56f-07b5-46e1-a527-85530e625800');
    }

    public function testCreateSecret()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/accounts/abcd1234/secrets', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('secret-management/create'));

        $secret = $this->accountClient->createSecret('abcd1234', 'example-4PI-secret');
        $this->assertInstanceOf(Secret::class, $secret);
    }

    public function testCreateSecretsServerError()
    {
        $this->expectException(Exception\Server::class);
        $this->nexmoClient->send(Argument::any())->willReturn($this->getGenericResponse('500', 500));
        $this->accountClient->createSecret('abcd1234', 'example-4PI-secret');
    }

    public function testCreateSecretsRequestError()
    {
        $this->expectException(Exception\Request::class);
        $this->nexmoClient->send(Argument::any())->willReturn($this->getGenericResponse('401', 401));
        $this->accountClient->createSecret('abcd1234', 'example-4PI-secret');
    }

    public function testCreateSecretsValidationError()
    {
        try {
            $this->nexmoClient->send(Argument::any())->willReturn($this->getResponse('secret-management/create-validation', 400));
            $this->accountClient->createSecret('abcd1234', 'example-4PI-secret');
        } catch (Validation $e) {
            $this->assertEquals('Bad Request: The request failed due to validation errors. See https://developer.nexmo.com/api-errors/account/secret-management#validation for more information', $e->getMessage());
            $this->assertEquals([['name' => 'secret', 'reason' => 'Does not meet complexity requirements']], $e->getValidationErrors());
        }
    }

    public function testDeleteSecret()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/accounts/abcd1234/secrets/ad6dc56f-07b5-46e1-a527-85530e625800', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('DELETE', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('secret-management/delete'));

        $this->accountClient->deleteSecret('abcd1234', 'ad6dc56f-07b5-46e1-a527-85530e625800');
    }

    public function testDeleteSecretsServerError()
    {
        $this->expectException(Exception\Server::class);
        $this->nexmoClient->send(Argument::any())->willReturn($this->getGenericResponse('500', 500));
        $this->accountClient->deleteSecret('abcd1234', 'ad6dc56f-07b5-46e1-a527-85530e625800');
    }

    public function testDeleteSecretsRequestError()
    {
        $this->expectException(Exception\Request::class);
        $this->nexmoClient->send(Argument::any())->willReturn($this->getGenericResponse('401', 401));
        $this->accountClient->deleteSecret('abcd1234', 'ad6dc56f-07b5-46e1-a527-85530e625800');
    }

    /**
     * Get the API response we'd expect for a call to the API.
     *
     * @param string $type
     * @return Response
     */
    protected function getResponse($type = 'success', $status = 200)
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'r'), $status);
    }

    protected function getGenericResponse($type = 'success', $status = 500)
    {
        return new Response(fopen(__DIR__. '/../responses/general/' . $type . '.json', 'r'), $status);
    }
}
