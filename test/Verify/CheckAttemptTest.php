<?php

declare(strict_types=1);

namespace VonageTest\Verify;

use DateTimeImmutable;
use Vonage\Verify\CheckAttempt;
use VonageTest\VonageTestCase;

class CheckAttemptTest extends VonageTestCase
{
    private function fixture(): array
    {
        return [
            'date_received' => '2016-05-15 03:58:11',
            'code' => '123456',
            'status' => 'INVALID',
            'ip_address' => '8.8.4.4',
        ];
    }

    public function testFromArrayPopulatesProperties(): void
    {
        $attempt = CheckAttempt::fromArray($this->fixture());

        $this->assertSame('123456', $attempt->code);
        $this->assertSame(CheckAttempt::INVALID, $attempt->status);
        $this->assertSame('8.8.4.4', $attempt->ipAddress);
    }

    public function testFromArrayParsesDate(): void
    {
        $attempt = CheckAttempt::fromArray($this->fixture());

        $this->assertInstanceOf(DateTimeImmutable::class, $attempt->dateReceived);
        $this->assertSame('2016-05-15', $attempt->dateReceived->format('Y-m-d'));
    }

    public function testEmptyIpAddressBecomesNull(): void
    {
        $data = $this->fixture();
        $data['ip_address'] = '';

        $attempt = CheckAttempt::fromArray($data);

        $this->assertNull($attempt->ipAddress);
    }

    public function testValidStatus(): void
    {
        $attempt = CheckAttempt::fromArray([
            'date_received' => '2016-05-15 03:58:11',
            'code' => '9876',
            'status' => CheckAttempt::VALID,
            'ip_address' => '',
        ]);

        $this->assertSame(CheckAttempt::VALID, $attempt->status);
    }

    public function testPropertiesAreReadonly(): void
    {
        $attempt = CheckAttempt::fromArray($this->fixture());

        $this->expectException(\Error::class);
        $attempt->code = 'changed'; // @phpstan-ignore-line
    }
}
