<?php

declare(strict_types=1);

namespace VonageTest\Verify;

use DateTimeImmutable;
use Vonage\Verify\CheckAttempt;
use Vonage\Verify\Verification;
use VonageTest\VonageTestCase;

class VerificationTest extends VonageTestCase
{
    private function fixture(): array
    {
        return [
            'request_id' => '44a5279b27dd4a638d614d265ad57a77',
            'account_id' => '6cff3913',
            'number' => '14845551212',
            'sender_id' => 'verify',
            'date_submitted' => '2016-05-15 03:55:05',
            'date_finalized' => '',
            'checks' => [
                ['date_received' => '2016-05-15 03:58:11', 'code' => '123456', 'status' => 'INVALID', 'ip_address' => ''],
                ['date_received' => '2016-05-15 03:55:50', 'code' => '1234', 'status' => 'INVALID', 'ip_address' => ''],
                ['date_received' => '2016-05-15 03:59:18', 'code' => '1234', 'status' => 'INVALID', 'ip_address' => '8.8.4.4'],
            ],
            'first_event_date' => '2016-05-15 03:55:05',
            'last_event_date' => '2016-05-15 03:57:12',
            'price' => '0.10000000',
            'currency' => 'EUR',
            'status' => 'FAILED',
        ];
    }

    public function testFromArrayPopulatesAllProperties(): void
    {
        $v = Verification::fromArray($this->fixture());

        $this->assertSame('44a5279b27dd4a638d614d265ad57a77', $v->requestId);
        $this->assertSame('6cff3913', $v->accountId);
        $this->assertSame('FAILED', $v->status);
        $this->assertSame('14845551212', $v->number);
        $this->assertSame('0.10000000', $v->price);
        $this->assertSame('EUR', $v->currency);
        $this->assertSame('verify', $v->senderId);
    }

    public function testFromArrayHydratesCheckAttempts(): void
    {
        $v = Verification::fromArray($this->fixture());

        $this->assertCount(3, $v->checks);
        $this->assertContainsOnlyInstancesOf(CheckAttempt::class, $v->checks);
        $this->assertSame('123456', $v->checks[0]->code);
        $this->assertSame(CheckAttempt::INVALID, $v->checks[0]->status);
        $this->assertSame('8.8.4.4', $v->checks[2]->ipAddress);
    }

    public function testFromArrayParsesDateSubmitted(): void
    {
        $v = Verification::fromArray($this->fixture());

        $this->assertInstanceOf(DateTimeImmutable::class, $v->dateSubmitted);
        $this->assertSame('2016-05-15', $v->dateSubmitted->format('Y-m-d'));
    }

    public function testFromArrayDateFinalizedIsNullWhenEmpty(): void
    {
        $v = Verification::fromArray($this->fixture());

        $this->assertNull($v->dateFinalized);
    }

    public function testFromArrayDateFinalizedIsSetWhenPopulated(): void
    {
        $data = $this->fixture();
        $data['date_finalized'] = '2016-05-15 04:00:00';

        $v = Verification::fromArray($data);

        $this->assertInstanceOf(DateTimeImmutable::class, $v->dateFinalized);
        $this->assertSame('2016-05-15', $v->dateFinalized->format('Y-m-d'));
    }

    public function testFromArrayParsesEventDates(): void
    {
        $v = Verification::fromArray($this->fixture());

        $this->assertInstanceOf(DateTimeImmutable::class, $v->firstEventDate);
        $this->assertInstanceOf(DateTimeImmutable::class, $v->lastEventDate);
    }

    public function testStatusConstants(): void
    {
        $this->assertSame('FAILED', Verification::STATUS_FAILED);
        $this->assertSame('SUCCESS', Verification::STATUS_SUCCESSFUL);
        $this->assertSame('EXPIRED', Verification::STATUS_EXPIRED);
        $this->assertSame('IN PROGRESS', Verification::STATUS_IN_PROGRESS);
        $this->assertSame('CANCELLED', Verification::STATUS_CANCELLED);
    }

    public function testPropertiesAreReadonly(): void
    {
        $v = Verification::fromArray($this->fixture());

        $this->expectException(\Error::class);
        $v->requestId = 'new-id'; // @phpstan-ignore-line
    }
}
