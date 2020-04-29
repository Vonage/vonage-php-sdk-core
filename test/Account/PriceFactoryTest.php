<?php
declare(strict_types=1);

namespace NexmoTest\Account;

use Nexmo\Account\PrefixPrice;
use Nexmo\Account\PriceFactory;
use PHPUnit\Framework\TestCase;
use Nexmo\Account\PriceHydrator;
use Nexmo\Account\SmsPrice;
use Nexmo\Account\VoicePrice;

class PriceFactoryTest extends TestCase
{
    /**
     * @var PriceFactory
     */
    protected $factory;

    public function setUp()
    {
        $this->factory = new PriceFactory(new PriceHydrator());
    }

    public function testCreatesPrefixPrice()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/responses/prefix-pricing.json'), true);
        $price = $this->factory->build($data, PriceFactory::TYPE_PREFIX);

        $this->assertTrue($price instanceof PrefixPrice);
    }

    public function testCreatesSmsPrice()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/responses/smsprice-us.json'), true);
        $price = $this->factory->build($data, PriceFactory::TYPE_SMS);

        $this->assertTrue($price instanceof SmsPrice);
    }

    public function testCreatesVoicePrice()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/responses/voiceprice-us.json'), true);
        $price = $this->factory->build($data, PriceFactory::TYPE_VOICE);

        $this->assertTrue($price instanceof VoicePrice);
    }

    public function testThrowsExceptionOnUnknownValue()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid pricing type requested');
        $this->expectExceptionCode(4);

        $data = [];
        $price = $this->factory->build($data, 4);
    }
}
