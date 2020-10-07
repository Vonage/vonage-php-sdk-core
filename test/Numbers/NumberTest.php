<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test\Numbers;

use PHPUnit\Framework\TestCase;
use Vonage\Application\Application;
use Vonage\Client\Exception\Exception;
use Vonage\Numbers\Number;

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

        self::assertEquals('14843331212', $number->getId());
        self::assertEquals('14843331212', $number->getMsisdn());
        self::assertEquals('14843331212', $number->getNumber());
    }

    public function testConstructWithIdAndCountry(): void
    {
        $number = new Number('14843331212', 'US');

        self::assertEquals('US', $number->getCountry());
    }

    public function testHydrate(): void
    {
        $data = json_decode(file_get_contents(__DIR__ . '/responses/single.json'), true);
        $this->number->fromArray($data['numbers'][0]);

        self::assertEquals('US', $this->number->getCountry());
        self::assertEquals('1415550100', $this->number->getNumber());
        self::assertEquals(Number::TYPE_MOBILE, $this->number->getType());
        self::assertEquals('http://example.com/message', $this->number->getWebhook(Number::WEBHOOK_MESSAGE));
        self::assertEquals('http://example.com/status', $this->number->getWebhook(Number::WEBHOOK_VOICE_STATUS));
        self::assertEquals('http://example.com/voice', $this->number->getVoiceDestination());
        self::assertEquals(Number::ENDPOINT_VXML, $this->number->getVoiceType());
        self::assertTrue($this->number->hasFeature(Number::FEATURE_VOICE));
        self::assertTrue($this->number->hasFeature(Number::FEATURE_SMS));
        self::assertContains(Number::FEATURE_VOICE, $this->number->getFeatures());
        self::assertContains(Number::FEATURE_SMS, $this->number->getFeatures());
        self::assertCount(2, $this->number->getFeatures());
    }

    public function testAvailableNumbers(): void
    {
        $data = json_decode(file_get_contents(__DIR__ . '/responses/available-numbers.json'), true);
        $this->number->fromArray($data['numbers'][0]);

        self::assertEquals('US', $this->number->getCountry());
        self::assertEquals('14155550100', $this->number->getNumber());
        self::assertEquals(Number::TYPE_MOBILE, $this->number->getType());
        self::assertEquals('0.67', $this->number->getCost());
        self::assertTrue($this->number->hasFeature(Number::FEATURE_VOICE));
        self::assertTrue($this->number->hasFeature(Number::FEATURE_SMS));
        self::assertContains(Number::FEATURE_VOICE, $this->number->getFeatures());
        self::assertContains(Number::FEATURE_SMS, $this->number->getFeatures());
        self::assertCount(2, $this->number->getFeatures());
    }

    /**
     * @throws Exception
     */
    public function testVoiceApplication(): void
    {
        $id = 'abcd-1234-edfg';

        $this->number->setVoiceDestination($id);
        $app = $this->number->getVoiceDestination();

        self::assertInstanceOf(Application::class, $app);
        self::assertEquals($id, $app->getId());
        self::assertArrayHas('app_id', $id, $this->number->getRequestData());

        $app = new Application($id);
        $this->number->setVoiceDestination($app);

        self::assertSame($app, $this->number->getVoiceDestination());
        self::assertArrayHas('app_id', $id, $this->number->getRequestData());
    }

    /**
     * @throws Exception
     */
    public function testForceVoiceType(): void
    {
        $this->number->setVoiceDestination('not-valid', NUMBER::ENDPOINT_SIP);

        self::assertSame(Number::ENDPOINT_SIP, $this->number->getVoiceType());
        self::assertArrayHas('voiceCallbackType', Number::ENDPOINT_SIP, $this->number->getRequestData());
    }

    /**
     * @dataProvider voiceDestinations
     * @param $type
     * @param $value
     * @throws Exception
     */
    public function testVoiceDestination($type, $value): void
    {
        self::assertSame($this->number, $this->number->setVoiceDestination($value));
        self::assertEquals($value, $this->number->getVoiceDestination());
        self::assertEquals($type, $this->number->getVoiceType());
        self::assertArrayHas('voiceCallbackType', $type, $this->number->getRequestData());
        self::assertArrayHas('voiceCallbackValue', $value, $this->number->getRequestData());
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

        self::assertEquals($numberData['type'], $number->getType());
    }

    /**
     * @throws Exception
     */
    public function testStatusWebhook(): void
    {
        self::assertSame($this->number, $this->number->setWebhook(Number::WEBHOOK_VOICE_STATUS, 'http://example.com'));
        self::assertEquals('http://example.com', $this->number->getWebhook(Number::WEBHOOK_VOICE_STATUS));
        self::assertArrayHas('voiceStatusCallbackUrl', 'http://example.com', $this->number->getRequestData());
    }

    /**
     * @throws Exception
     */
    public function testMessageWebhook(): void
    {
        self::assertSame($this->number, $this->number->setWebhook(Number::WEBHOOK_MESSAGE, 'http://example.com'));
        self::assertEquals('http://example.com', $this->number->getWebhook(Number::WEBHOOK_MESSAGE));
        self::assertArrayHas('moHttpUrl', 'http://example.com', $this->number->getRequestData());
    }

    public static function assertArrayHas($key, $value, $array): void
    {
        self::assertArrayHasKey($key, $array);
        self::assertEquals($value, $array[$key]);
    }
}
