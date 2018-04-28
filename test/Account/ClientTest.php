<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Account;

use Nexmo\Account\Balance;
use Nexmo\Account\Secret;
use Nexmo\Account\SecretCollection;
use Nexmo\Network;
use Nexmo\Account\SmsPrice;
use Nexmo\Account\VoicePrice;
use Nexmo\Account\Client;
use Zend\Diactoros\Response;
use NexmoTest\Psr7AssertionTrait;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use PHPUnit\Framework\TestCase;
use Nexmo\Client\Exception;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    protected $nexmoClient;

    /**
     * @var Client
     */
    protected $accountClient;

    public function setUp()
    {
        $this->nexmoClient = $this->prophesize('Nexmo\Client');
        $this->nexmoClient->getRestUrl()->willReturn('https://rest.nexmo.com');
        $this->nexmoClient->getApiUrl()->willReturn('https://api.nexmo.com');
        $this->accountClient = new Client();
        $this->accountClient->setClient($this->nexmoClient->reveal());
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
        $this->assertInstanceOf(Network::class, $smsPrice['networks']['311310']);
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
        $this->assertInstanceOf(Network::class, $voicePrice['networks']['311310']);
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
        } catch (Exception\Validation $e) {
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
