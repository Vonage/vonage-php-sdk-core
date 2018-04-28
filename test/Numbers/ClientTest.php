<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Numbers;

use Nexmo\Numbers\Client;
use Nexmo\Numbers\Number;
use Nexmo\Client\Exception;
use NexmoTest\Psr7AssertionTrait;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Response;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    protected $nexmoClient;

    /**
     * @var Client
     */
    protected $numberClient;

    public function setUp()
    {
        $this->nexmoClient = $this->prophesize('Nexmo\Client');
        $this->nexmoClient->getRestUrl()->willReturn('https://rest.nexmo.com');
        $this->numberClient = new Client();
        $this->numberClient->setClient($this->nexmoClient->reveal());
    }

    /**
     * @dataProvider updateNumber
     */
    public function testUpdateNumber($payload, $id, $expectedId, $lookup)
    {
        //based on the id provided, may need to look up the number first
        if($lookup){
            if(is_null($id) OR ('1415550100' == $id)){
                $first = $this->getResponse('single');
            } else {
                $first = $this->getResponse('single-update');
            }

            $second = $this->getResponse('post');
            $third = $this->getResponse('single');
        } else {
            $first = $this->getResponse('post');
            $second = $this->getResponse('single');
            $third = null;
        }

        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($expectedId, $lookup) {
            if($request->getUri()->getPath() == '/account/numbers'){
                //just getting the number first / last
                return true;
            }

            $this->assertEquals('/number/update', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            
            $this->assertRequestFormBodyContains('country', 'US', $request);
            $this->assertRequestFormBodyContains('msisdn', $expectedId, $request);


            $this->assertRequestFormBodyContains('moHttpUrl', 'https://example.com/new_message', $request);
            $this->assertRequestFormBodyContains('voiceCallbackType', 'vxml', $request);
            $this->assertRequestFormBodyContains('voiceCallbackValue', 'https://example.com/new_voice', $request);
            $this->assertRequestFormBodyContains('voiceStatusCallbackUrl' , 'https://example.com/new_status' , $request);

            return true;
        }))->willReturn($first, $second, $third);

        if(isset($id)){
            $number = $this->numberClient->update($payload, $id);
        } else {
            $number = $this->numberClient->update($payload);
        }

        $this->assertInstanceOf('Nexmo\Numbers\Number', $number);
        if($payload instanceof Number){
            $this->assertSame($payload, $number);

        }
    }

    public function updateNumber()
    {

        $raw = $rawId = [
            'moHttpUrl' => 'https://example.com/new_message',
            'voiceCallbackType' => 'vxml',
            'voiceCallbackValue' => 'https://example.com/new_voice',
            'voiceStatusCallbackUrl' => 'https://example.com/new_status'
        ];

        $rawId['country'] = 'US';
        $rawId['msisdn'] = '1415550100';

        $number = new Number('1415550100');
        $number->setWebhook(Number::WEBHOOK_MESSAGE, 'https://example.com/new_message');
        $number->setWebhook(Number::WEBHOOK_VOICE_STATUS, 'https://example.com/new_status');
        $number->setVoiceDestination('https://example.com/new_voice');

        $noLookup = new Number('1415550100', 'US');
        $noLookup->setWebhook(Number::WEBHOOK_MESSAGE, 'https://example.com/new_message');
        $noLookup->setWebhook(Number::WEBHOOK_VOICE_STATUS, 'https://example.com/new_status');
        $noLookup->setVoiceDestination('https://example.com/new_voice');

        $fresh = new Number('1415550100', 'US');
        $fresh->setWebhook(Number::WEBHOOK_MESSAGE, 'https://example.com/new_message');
        $fresh->setWebhook(Number::WEBHOOK_VOICE_STATUS, 'https://example.com/new_status');
        $fresh->setVoiceDestination('https://example.com/new_voice');
        
        return [
            [$raw, '1415550100', '1415550100', true],
            [$rawId, null, '1415550100', false],
            [clone $number, null, '1415550100', true],
            [clone $number, '1415550100', '1415550100', true],
            [clone $noLookup, null, '1415550100', false],
            [clone $fresh, '1415550100', '1415550100', true],
        ];
    }

    /**
     * @dataProvider numbers
     */
    public function testGetNumber($payload, $id)
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($id){
            $this->assertEquals('/account/numbers', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            $this->assertRequestQueryContains('pattern', $id, $request);
            return true;
        }))->willReturn($this->getResponse('single'));

        $number = $this->numberClient->get($payload);

        $this->assertInstanceOf('Nexmo\Numbers\Number', $number);
        if($payload instanceof Number){
            $this->assertSame($payload, $number);
        }

        $this->assertSame($id, $number->getId());
    }

    public function numbers()
    {
        return [
            ['1415550100', '1415550100'],
            [new Number('1415550100'), '1415550100'],
        ];
    }

    public function testListNumbers()
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request){
            $this->assertEquals('/account/numbers', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('list'));

        $numbers = $this->numberClient->search();

        $this->assertInternalType('array', $numbers);
        $this->assertInstanceOf('Nexmo\Numbers\Number', $numbers[0]);
        $this->assertInstanceOf('Nexmo\Numbers\Number', $numbers[1]);

        $this->assertSame('14155550100', $numbers[0]->getId());
        $this->assertSame('14155550101', $numbers[1]->getId());
    }

    public function testSearchAvailablePassesThroughWhitelistedOptions()
    {

        $allowedOptions = [
            'pattern' => 'one',
            'search_pattern' => '2',
            'features' => 'SMS,VOICE',
            'size' => '100',
            'index' => '19'
        ];
        $invalidOptions = ['foo' => 'bananas'];

        $options = array_merge($allowedOptions, $invalidOptions);

        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($allowedOptions, $invalidOptions){
            $this->assertEquals('/number/search', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());

            // Things that are whitelisted should be shown
            foreach ($allowedOptions as $name => $value) {
                $this->assertRequestQueryContains($name, $value, $request);
            }

            // Anything else should be dropped
            foreach ($invalidOptions as $name => $value) {
                $this->assertRequestQueryNotContains($name, $request);
            }
            return true;
        }))->willReturn($this->getResponse('available-numbers'));

        $this->numberClient->searchAvailable('US', $options);
    }

    public function testSearchAvailableReturnsNumberList()
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request){
            $this->assertEquals('/number/search', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('available-numbers'));

        $numbers = $this->numberClient->searchAvailable('US');

        $this->assertInternalType('array', $numbers);
        $this->assertInstanceOf('Nexmo\Numbers\Number', $numbers[0]);
        $this->assertInstanceOf('Nexmo\Numbers\Number', $numbers[1]);

        $this->assertSame('14155550100', $numbers[0]->getId());
        $this->assertSame('14155550101', $numbers[1]->getId());
    }

    public function testSearchOwnedErrorsOnUnknownSearchParameters()
    {

        $this->expectException(Exception\Request::class);
        $this->expectExceptionMessage("Unknown option: 'foo'");
        
        $this->numberClient->searchOwned('1415550100', [
            'foo' => 'bar',
        ]);
    }

    public function testSearchOwnedPassesInAllowedAdditionalParameters()
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request){
            $this->assertEquals('/account/numbers', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            $this->assertEquals('pattern=1415550100&index=1&size=100&search_pattern=0', $request->getUri()->getQuery());
            return true;
        }))->willReturn($this->getResponse('single'));

        $this->numberClient->searchOwned('1415550100', [
            'index' => 1,
            'size' => '100',
            'search_pattern' => 0
        ]);
    }

    public function testSearchOwnedReturnsSingleNumber()
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request){
            $this->assertEquals('/account/numbers', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('single'));

        $numbers = $this->numberClient->searchOwned('1415550100');

        $this->assertInternalType('array', $numbers);
        $this->assertInstanceOf('Nexmo\Numbers\Number', $numbers[0]);

        $this->assertSame('1415550100', $numbers[0]->getId());
    }

    public function testPurchaseNumberWithNumberObject()
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request){
            $this->assertEquals('/number/buy', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('post'));

        $number = new Number('1415550100', 'US');
        $this->numberClient->purchase($number);

        // There's nothing to assert here as we don't do anything with the response.
        // If there's no exception thrown, everything is fine!
    }

    public function testPurchaseNumberWithNumberAndCountry()
    {
        // When providing a number string, the first thing that happens is a GET request to fetch number details
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request){
            return $request->getUri()->getPath() === '/account/numbers';
        }))->willReturn($this->getResponse('single'));

        // Then we purchase the number
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request){
            if ($request->getUri()->getPath() === '/number/buy') {
                $this->assertEquals('/number/buy', $request->getUri()->getPath());
                $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
                $this->assertEquals('POST', $request->getMethod());
                return true;
            }
            return false;
        }))->willReturn($this->getResponse('post'));
        $this->numberClient->purchase('1415550100', 'US');

        // There's nothing to assert here as we don't do anything with the response.
        // If there's no exception thrown, everything is fine!
    }

    /**
     * @dataProvider purchaseNumberErrorProvider
     */
    public function testPurchaseNumberErrors($number, $country, $responseFile, $expectedHttpCode, $expectedException, $expectedExceptionMessage)
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request){
            $this->assertEquals('/number/buy', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse($responseFile, $expectedHttpCode));

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $num = new Number($number, $country);
        $this->numberClient->purchase($num);
    }

    public function purchaseNumberErrorProvider()
    {
        $r = [];

        $r['mismatched number/country'] = ['14155510100', 'GB', 'method-failed', 420, Exception\Request::class, 'method failed'];
        $r['user already owns number'] = ['14155510100', 'GB', 'method-failed', 420, Exception\Request::class, 'method failed'];
        $r['someone else owns the number'] = ['14155510100', 'GB', 'method-failed', 420, Exception\Request::class, 'method failed'];

        return $r;
    }

    public function testCancelNumberWithNumberObject()
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request){
            $this->assertEquals('/number/cancel', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('cancel'));

        $number = new Number('1415550100', 'US');
        $this->numberClient->cancel($number);

        // There's nothing to assert here as we don't do anything with the response.
        // If there's no exception thrown, everything is fine!
    }

    public function testCancelNumberWithNumberString()
    {

        // When providing a number string, the first thing that happens is a GET request to fetch number details
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request){
            return $request->getUri()->getPath() === '/account/numbers';
        }))->willReturn($this->getResponse('single'));


        // Then we get a POST request to cancel
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) {
            if ($request->getUri()->getPath() === '/number/cancel') {
                $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
                $this->assertEquals('POST', $request->getMethod());
                return true;
            }
            return false;
        }))->willReturn($this->getResponse('cancel'));

        $this->numberClient->cancel('1415550100');

        // There's nothing to assert here as we don't do anything with the response.
        // If there's no exception thrown, everything is fine!
    }

    public function testCancelNumberError()
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request){
            $this->assertEquals('/number/cancel', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('method-failed', 420));

        $this->expectException(Exception\Request::class);
        $this->expectExceptionMessage('method failed');

        $num = new Number('1415550100', 'US');
        $this->numberClient->cancel($num);
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
