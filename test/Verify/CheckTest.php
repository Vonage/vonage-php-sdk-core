<?php

declare(strict_types=1);

namespace VonageTest\Verify;

use Vonage\Verify\Check;
use VonageTest\VonageTestCase;

class CheckTest extends VonageTestCase
{
    private function fixture(): array
    {
        return [
            'request_id' => 'de37150c89584f36a18925181d62627c',
            'status' => '0',
            'event_id' => '02000000D8C4D977',
            'price' => '0.10000000',
            'currency' => 'EUR',
        ];
    }

    public function testFromArrayPopulatesProperties(): void
    {
        $check = Check::fromArray($this->fixture());

        $this->assertSame('de37150c89584f36a18925181d62627c', $check->requestId);
        $this->assertSame('02000000D8C4D977', $check->eventId);
        $this->assertSame('0', $check->status);
        $this->assertSame('0.10000000', $check->price);
        $this->assertSame('EUR', $check->currency);
    }

    public function testPropertiesAreReadonly(): void
    {
        $check = Check::fromArray($this->fixture());

        $this->expectException(\Error::class);
        $check->requestId = 'changed'; // @phpstan-ignore-line
    }
}
