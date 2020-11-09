<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Numbers;

use PHPUnit\Framework\TestCase;
use Vonage\Application\Application;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Numbers\Number;

use function file_get_contents;
use function json_decode;

class NumberTest extends TestCase
{
    /**
     * @var Number;
     */
    protected $number;

    public function setUp(): void
    {
        $this->number = new Number();
    }

    public function testConstructWithId(): void
    {
        $number = new Number('14843331212');

        $this->assertEquals('14843331212', $number->getId());
        $this->assertEquals('14843331212', $number->getMsisdn());
        $this->assertEquals('14843331212', $number->getNumber());
    }

    public function testConstructWithIdAndCountry(): void
    {
        $number = new Number('14843331212', 'US');

        $this->assertEquals('US', $number->getCountry());
    }

    public function testHydrate(): void
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
        $this->assertContains(Number::FEATURE_VOICE, $this->number->getFeatures());
        $this->assertContains(Number::FEATURE_SMS, $this->number->getFeatures());
        $this->assertCount(2, $this->number->getFeatures());
    }

    public function testAvailableNumbers(): void
    {
        $data = json_decode(file_get_contents(__DIR__ . '/responses/available-numbers.json'), true);
        $this->number->fromArray($data['numbers'][0]);

        $this->assertEquals('US', $this->number->getCountry());
        $this->assertEquals('14155550100', $this->number->getNumber());
        $this->assertEquals(Number::TYPE_MOBILE, $this->number->getType());
        $this->assertEquals('0.67', $this->number->getCost());
        $this->assertTrue($this->number->hasFeature(Number::FEATURE_VOICE));
        $this->assertTrue($this->number->hasFeature(Number::FEATURE_SMS));
        $this->assertContains(Number::FEATURE_VOICE, $this->number->getFeatures());
        $this->assertContains(Number::FEATURE_SMS, $this->number->getFeatures());
        $this->assertCount(2, $this->number->getFeatures());
    }

    /**
     * @throws ClientException
     */
    public function testVoiceApplication(): void
    {
        $id = 'abcd-1234-edfg';

        $this->number->setVoiceDestination($id);
        $app = $this->number->getVoiceDestination();

        $this->assertInstanceOf(Application::class, $app);
        $this->assertEquals($id, $app->getId());
        $this->assertArrayHas('app_id', $id, $this->number->getRequestData());

        $app = new Application($id);
        $this->number->setVoiceDestination($app);

        $this->assertSame($app, $this->number->getVoiceDestination());
        $this->assertArrayHas('app_id', $id, $this->number->getRequestData());
    }

    /**
     * @throws ClientException
     */
    public function testForceVoiceType(): void
    {
        $this->number->setVoiceDestination('not-valid', NUMBER::ENDPOINT_SIP);

        $this->assertSame(Number::ENDPOINT_SIP, $this->number->getVoiceType());
        $this->assertArrayHas('voiceCallbackType', Number::ENDPOINT_SIP, $this->number->getRequestData());
    }

    /**
     * @dataProvider voiceDestinations
     *
     * @param $type
     * @param $value
     *
     * @throws ClientException
     */
    public function testVoiceDestination($type, $value): void
    {
        $this->assertSame($this->number, $this->number->setVoiceDestination($value));
        $this->assertEquals($value, $this->number->getVoiceDestination());
        $this->assertEquals($type, $this->number->getVoiceType());
        $this->assertArrayHas('voiceCallbackType', $type, $this->number->getRequestData());
        $this->assertArrayHas('voiceCallbackValue', $value, $this->number->getRequestData());
    }

    /**
     * @return array[]
     */
    public function voiceDestinations(): array
    {
        return [
            [Number::ENDPOINT_SIP, 'user@example.com'],
            [Number::ENDPOINT_TEL, '14843331212'],
            [Number::ENDPOINT_VXML, 'http://example.com']
        ];
    }

    public function testSystemType(): void
    {
        $numberData = [
            'msisdn' => '447700900000',
            'type' => Number::TYPE_FIXED,
        ];
        $number = new Number();
        $number->fromArray($numberData);

        $this->assertEquals($numberData['type'], $number->getType());
    }

    /**
     * @throws ClientException
     */
    public function testStatusWebhook(): void
    {
        $this->assertSame($this->number, $this->number->setWebhook(Number::WEBHOOK_VOICE_STATUS, 'http://example.com'));
        $this->assertEquals('http://example.com', $this->number->getWebhook(Number::WEBHOOK_VOICE_STATUS));
        $this->assertArrayHas('voiceStatusCallbackUrl', 'http://example.com', $this->number->getRequestData());
    }

    /**
     * @throws ClientException
     */
    public function testMessageWebhook(): void
    {
        $this->assertSame($this->number, $this->number->setWebhook(Number::WEBHOOK_MESSAGE, 'http://example.com'));
        $this->assertEquals('http://example.com', $this->number->getWebhook(Number::WEBHOOK_MESSAGE));
        $this->assertArrayHas('moHttpUrl', 'http://example.com', $this->number->getRequestData());
    }

    public static function assertArrayHas($key, $value, $array): void
    {
        self::assertArrayHasKey($key, $array);
        self::assertEquals($value, $array[$key]);
    }
}
