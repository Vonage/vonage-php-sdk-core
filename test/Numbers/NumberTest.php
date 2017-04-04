<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Numbers;


use Nexmo\Application\Application;
use Nexmo\Numbers\Number;

class NumberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Nexmo\Numbers\Number;
     */
    protected $number;

    public function setUp()
    {
        $this->number = new Number();
    }

    public function testConstructWithId()
    {
        $number = new Number('14843331212');

        $this->assertEquals('14843331212', $number->getId());
        $this->assertEquals('14843331212', $number->getMsisdn());
        $this->assertEquals('14843331212', $number->getNumber());
    }

    public function testConstructWithIdAndCountry()
    {
        $number = new Number('14843331212', 'US');
        $this->assertEquals('US', $number->getCountry());
    }

    public function testHydrate()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/responses/single.json'), true);
        $this->number->jsonUnserialize($data['numbers'][0]);

        $this->assertEquals('US', $this->number->getCountry());
        $this->assertEquals('12404284163', $this->number->getNumber());
        $this->assertEquals(Number::TYPE_MOBILE, $this->number->getType());

        $this->assertEquals('http://example.com/message', $this->number->getWebhook(Number::WEBHOOK_MESSAGE));
        $this->assertEquals('http://example.com/status', $this->number->getWebhook(Number::WEBHOOK_VOICE_STATUS));

        $this->assertEquals('http://example.com/voice', $this->number->getVoiceDestination());
        $this->assertEquals(Number::ENDPOINT_VXML, $this->number->getVoiceType());

        $this->assertTrue($this->number->hasFeature(Number::FEATURE_VOICE));
        $this->assertTrue($this->number->hasFeature(Number::FEATURE_SMS));

        $this->assertTrue(in_array(Number::FEATURE_VOICE, $this->number->getFeatures()));
        $this->assertTrue(in_array(Number::FEATURE_SMS, $this->number->getFeatures()));

        $this->assertCount(2, $this->number->getFeatures());
    }

    public function testVoiceApplication()
    {
        $id = 'abcd-1234-edfg';

        $this->number->setVoiceDestination($id);
        $app = $this->number->getVoiceDestination();
        $this->assertInstanceOf('Nexmo\Application\Application', $app);
        $this->assertEquals($id, $app->getId());

        $this->assertArrayHas('voiceCallbackType',  Number::ENDPOINT_APP,  $this->number->getRequestData());
        $this->assertArrayHas('voiceCallbackValue', $id, $this->number->getRequestData());

        $app = new Application($id);
        $this->number->setVoiceDestination($app);
        $this->assertSame($app, $this->number->getVoiceDestination());

        $this->assertArrayHas('voiceCallbackType',  Number::ENDPOINT_APP,  $this->number->getRequestData());
        $this->assertArrayHas('voiceCallbackValue', $id, $this->number->getRequestData());
    }

    public function testForceVoiceType()
    {
        $this->number->setVoiceDestination('not-valid', NUMBER::ENDPOINT_SIP);
        $this->assertSame(Number::ENDPOINT_SIP, $this->number->getVoiceType());
        $this->assertArrayHas('voiceCallbackType', Number::ENDPOINT_SIP, $this->number->getRequestData());
    }

    /**
     * @dataProvider voiceDestinations
     */
    public function testVoiceDestination($type, $value)
    {
        $this->assertSame($this->number, $this->number->setVoiceDestination($value));

        $this->assertEquals($value, $this->number->getVoiceDestination());
        $this->assertEquals($type, $this->number->getVoiceType());

        $this->assertArrayHas('voiceCallbackType',  $type,  $this->number->getRequestData());
        $this->assertArrayHas('voiceCallbackValue', $value, $this->number->getRequestData());
    }

    public function voiceDestinations()
    {
        return [
            [Number::ENDPOINT_SIP, 'user@example.com'],
            [Number::ENDPOINT_TEL, '14843331212'],
            [Number::ENDPOINT_VXML, 'http://example.com']
        ];
    }

    public function testSystemType()
    {
        $this->markTestIncomplete('not tested');
        $this->assertSame($this->number, $this->number->setSystemType('inbound'));
        $this->assertEquals('inbound', $this->number->getSystemType());
    }

    public function testStatusWebhook()
    {
        $this->assertSame($this->number, $this->number->setWebhook(Number::WEBHOOK_VOICE_STATUS, 'http://example.com'));
        $this->assertEquals('http://example.com', $this->number->getWebhook(Number::WEBHOOK_VOICE_STATUS));
        $this->assertArrayHas('voiceStatusCallbackUrl', 'http://example.com', $this->number->getRequestData());
    }

    public function testMessageWebhook()
    {
        $this->assertSame($this->number, $this->number->setWebhook(Number::WEBHOOK_MESSAGE, 'http://example.com'));
        $this->assertEquals('http://example.com', $this->number->getWebhook(Number::WEBHOOK_MESSAGE));
        $this->assertArrayHas('moHttpUrl', 'http://example.com', $this->number->getRequestData());
    }

    public static function assertArrayHas($key, $value, $array)
    {
        self::assertArrayHasKey($key, $array);
        self::assertEquals($value, $array[$key]);
    }
}
