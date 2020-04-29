<?php
declare(strict_types=1);

namespace NexmoTest\Account;

use Nexmo\Account\PriceHydrator;
use Nexmo\Account\VoicePrice;
use PHPUnit\Framework\TestCase;

class PriceHydratorTest extends TestCase
{
    public function testCanHydratorPriceObject()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/responses/voiceprice-us.json'), true);

        $hydrator = new PriceHydrator();
        $price = new VoicePrice();

        $price = $hydrator->hydrateObject($data, $price);

        $this->assertTrue($price instanceof $price);
    }

    public function testThrowsExceptionWhenCallingHydratorAlone()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Cannot hydrator with a default object, use hydrateObject() or PriceFactory::build()'
        );

        $data = ['this' => 'fails'];
        $hydrator = new PriceHydrator();
        $price = $hydrator->hydrate($data);
    }
}
