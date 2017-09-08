<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Account;

use Nexmo\Account\Balance;
use Nexmo\Network;
use Nexmo\Account\SmsPrice;
use Nexmo\Account\VoicePrice;
use Nexmo\Account\Client;
use Zend\Diactoros\Response;
use NexmoTest\Psr7AssertionTrait;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;


class ClientTest extends \PHPUnit_Framework_TestCase
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
        $this->accountClient = new Client();
        $this->accountClient->setClient($this->nexmoClient->reveal());

        $basic  = new \Nexmo\Client\Credentials\Basic('7c9738e6','5e840647a5b710a011A');
        $client = new \Nexmo\Client($basic);
        //$this->accountClient->setClient($client);
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
}
