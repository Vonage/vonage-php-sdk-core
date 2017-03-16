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
use NexmoTest\Psr7AssertionTrait;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Response;

class ClientTest extends \PHPUnit_Framework_TestCase
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
            if(is_null($id) OR ('12404284163' == $id)){
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
        $rawId['msisdn'] = '12404284163';

        $number = new Number('12404284163');
        $number->setWebhook(Number::WEBHOOK_MESSAGE, 'https://example.com/new_message');
        $number->setWebhook(Number::WEBHOOK_VOICE_STATUS, 'https://example.com/new_status');
        $number->setVoiceDestination('https://example.com/new_voice');

        $noLookup = new Number('12404284163', 'US');
        $noLookup->setWebhook(Number::WEBHOOK_MESSAGE, 'https://example.com/new_message');
        $noLookup->setWebhook(Number::WEBHOOK_VOICE_STATUS, 'https://example.com/new_status');
        $noLookup->setVoiceDestination('https://example.com/new_voice');

        $fresh = new Number('12404284163', 'US');
        $fresh->setWebhook(Number::WEBHOOK_MESSAGE, 'https://example.com/new_message');
        $fresh->setWebhook(Number::WEBHOOK_VOICE_STATUS, 'https://example.com/new_status');
        $fresh->setVoiceDestination('https://example.com/new_voice');
        
        return [
            [$raw, '12404284163', '12404284163', true],
            [$rawId, null, '12404284163', false],
            [clone $number, null, '12404284163', true],
            [clone $number, '14845551212', '14845551212', true],
            [clone $noLookup, null, '12404284163', false],
            [clone $fresh, '12404284163', '12404284163', true],
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
            ['12404284163', '12404284163'],
            [new Number('12404284163'), '12404284163'],
        ];
    }

    /**
     * @dataProvider searchNumbers
     */
    public function testSearch($country, $pattern = null, $searchPattern = null, $features = null, $size = null, $index = null)
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($country, $pattern, $searchPattern, $features, $size, $index) {
            $this->assertEquals('/number/search', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            $this->assertRequestQueryContains('country', $country, $request);
            if ($pattern) {
                $this->assertRequestQueryContains('pattern', $pattern, $request);
            }
            if ($searchPattern) {
                $this->assertRequestQueryContains('search_pattern', $searchPattern, $request);
            }
            if ($features) {
                if (is_array($features)) {
                    $features = implode($features, ',');
                }
                $this->assertRequestQueryContains('features', $features, $request);
            }
            if ($size) {
                $this->assertRequestQueryContains('size', $size, $request);
            }
            if ($index) {
                $this->assertRequestQueryContains('index', $index, $request);
            }

            return true;
        }))->willReturn($this->getResponse('search'));

        $collection = $this->numberClient->search($country, $pattern, $searchPattern, $features, $size, $index);
        $this->assertCount(2, $collection);

        $n1 = $collection[0];
        $n2 = $collection[1];

        $this->assertEquals('FR', $n1->getCountry());
        $this->assertEquals('33644630604', $n1->getId());
        $this->assertEquals('0.50', $n1->getCost());
        $this->assertEquals('mobile-lvn', $n1->getType());
        $this->assertEquals(['VOICE', 'SMS'], $n1->getFeatures());

        $this->assertEquals('FR', $n2->getCountry());
        $this->assertEquals('33644630605', $n2->getId());
        $this->assertEquals('0.50', $n2->getCost());
        $this->assertEquals('mobile-lvn', $n2->getType());
        $this->assertEquals(['VOICE', 'SMS'], $n2->getFeatures());
    }

    public function searchNumbers()
    {
        return [
            ['FR'],
            ['FR', '54', '345', Number::FEATURE_SMS, '20', '10'],
            ['FR', null, null, [Number::FEATURE_SMS]],
            ['FR', null, null, [Number::FEATURE_SMS, Number::FEATURE_VOICE]],
        ];
    }

    public function testBuy()
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) {
            $this->assertEquals('/number/buy', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestQueryContains('country', 'FR', $request);
            $this->assertRequestQueryContains('msisdn', '33644630605', $request);

            return true;
        }))->willReturn($this->getResponse('empty'));

        $this->numberClient->buy('FR', '33644630605');
    }

    public function testCancel()
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) {
            $this->assertEquals('/number/cancel', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestQueryContains('country', 'FR', $request);
            $this->assertRequestQueryContains('msisdn', '33644630605', $request);

            return true;
        }))->willReturn($this->getResponse('empty'));

        $this->numberClient->cancel('FR', '33644630605');
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
