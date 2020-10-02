<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Numbers;


use Vonage\Application\Application;
use Vonage\Numbers\Number;
use PHPUnit\Framework\TestCase;

class NumberTest extends TestCase
{
    /**
     * @var \Vonage\Numbers\Number;
     */
    protected $number;

    public function setUp(): void
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
        $this->number->fromArray($data['numbers'][0]);

        $this->assertEquals('US', $this->number->getCountry());
        $this->assertEquals('1415550100', $this->number->getNumber());
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

    public function testAvailableNumbers()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/responses/available-numbers.json'), true);
        $this->number->fromArray($data['numbers'][0]);

        $this->assertEquals('US', $this->number->getCountry());
        $this->assertEquals('14155550100', $this->number->getNumber());
        $this->assertEquals(Number::TYPE_MOBILE, $this->number->getType());
        $this->assertEquals('0.67', $this->number->getCost());

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
        $this->assertInstanceOf('Vonage\Application\Application', $app);
        $this->assertEquals($id, $app->getId());

        $this->assertArrayHas('app_id', $id, $this->number->getRequestData());

        $app = new Application($id);
        $this->number->setVoiceDestination($app);
        $this->assertSame($app, $this->number->getVoiceDestination());

        $this->assertArrayHas('app_id', $id, $this->number->getRequestData());
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
        $numberData = [
            'msisdn' => '447700900000',
            'type' => Number::TYPE_FIXED,
        ];
        $number = new Number();
        $number->fromArray($numberData);

        $this->assertEquals($numberData['type'], $number->getType());
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
