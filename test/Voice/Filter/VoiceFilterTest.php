<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Voice\Filter;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Vonage\Voice\Filter\VoiceFilter;

class VoiceFilterTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testQueryHasStartDate(): void
    {
        $filter = new VoiceFilter();
        $filter->setDateStart(new DateTimeImmutable('2020-01-01', new DateTimeZone('Z')));
        $query = $filter->getQuery();

        $this->assertSame('2020-01-01T00:00:00Z', $query['date_start']);
    }

    /**
     * @throws Exception
     */
    public function testQueryHasEndDate(): void
    {
        $query = (new VoiceFilter())
            ->setDateEnd(new DateTimeImmutable('2020-01-01', new DateTimeZone('Z')))
            ->getQuery();

        $this->assertSame('2020-01-01T00:00:00Z', $query['date_end']);
    }

    public function testQueryHasConversationUUID(): void
    {
        $query = (new VoiceFilter())
            ->setConversationUUID('CON-c39bc0bb-7ebc-405f-801b-f6b9a0d92860')
            ->getQuery();

        $this->assertSame('CON-c39bc0bb-7ebc-405f-801b-f6b9a0d92860', $query['conversation_uuid']);
    }

    public function testCanSetRecordIndex(): void
    {
        $query = (new VoiceFilter())
            ->setRecordIndex(100)
            ->getQuery();

        $this->assertSame(100, $query['record_index']);
    }

    public function testCanSetPageSize(): void
    {
        $query = (new VoiceFilter())
            ->setPageSize(100)
            ->getQuery();

        $this->assertSame(100, $query['page_size']);
    }

    public function testCanSetOrder(): void
    {
        $query = (new VoiceFilter())
            ->setOrder(VoiceFilter::ORDER_ASC)
            ->getQuery();

        $this->assertSame(VoiceFilter::ORDER_ASC, $query['order']);
    }

    public function testFilterThrowExceptionOnBadOrder(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Order must be `asc` or `desc`');

        (new VoiceFilter())->setOrder('foo');
    }

    public function testStartDateTimezoneIsSwitchedToUTC()
    {
        $filter = new VoiceFilter();
        $filter->setDateStart(new DateTimeImmutable('2020-01-01', new DateTimeZone('America/New_York')));

        $startDate = $filter->getDateStart();
        $this->assertSame('Z', $startDate->getTimezone()->getName());
    }

    public function testEndDateTimezoneIsSwitchedToUTC()
    {
        $filter = new VoiceFilter();
        $filter->setDateEnd(new DateTimeImmutable('2020-01-01', new DateTimeZone('America/New_York')));

        $startDate = $filter->getDateEnd();
        $this->assertSame('Z', $startDate->getTimezone()->getName());
    }
}
