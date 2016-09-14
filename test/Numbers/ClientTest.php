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
