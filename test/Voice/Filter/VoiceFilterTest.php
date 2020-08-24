<?php
declare(strict_types=1);

namespace VonageTest\Voice\Filter;

use Vonage\Voice\Filter\VoiceFilter;
use PHPUnit\Framework\TestCase;

class VoiceFilterTest extends TestCase
{
    public function testQueryHasStartDate()
    {
        $filter = new VoiceFilter();
        $filter->setDateStart(new \DateTimeImmutable('2020-01-01', new \DateTimeZone('Z')));

        $query = $filter->getQuery();
        $this->assertSame('2020-01-01T00:00:00Z', $query['date_start']);
    }

    public function testQueryHasEndDate()
    {
        $filter = new VoiceFilter();
        $filter->setDateEnd(new \DateTimeImmutable('2020-01-01', new \DateTimeZone('Z')));

        $query = $filter->getQuery();
        $this->assertSame('2020-01-01T00:00:00Z', $query['date_end']);
    }

    public function testQueryHasConversationUUID()
    {
        $filter = new VoiceFilter();
        $filter->setConversationUUID('CON-c39bc0bb-7ebc-405f-801b-f6b9a0d92860');

        $query = $filter->getQuery();
        $this->assertSame('CON-c39bc0bb-7ebc-405f-801b-f6b9a0d92860', $query['conversation_uuid']);
    }

    public function testCanSetRecordIndex()
    {
        $filter = new VoiceFilter();
        $filter->setRecordIndex(100);

        $query = $filter->getQuery();
        $this->assertSame(100, $query['record_index']);
    }

    public function testCanSetPageSize()
    {
        $filter = new VoiceFilter();
        $filter->setPageSize(100);

        $query = $filter->getQuery();
        $this->assertSame(100, $query['page_size']);
    }

    public function testCanSetOrder()
    {
        $filter = new VoiceFilter();
        $filter->setOrder(VoiceFilter::ORDER_ASC);

        $query = $filter->getQuery();
        $this->assertSame(VoiceFilter::ORDER_ASC, $query['order']);
    }

    public function testFilterThrowExceptionOnBadOrder()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Order must be `asc` or `desc`');

        $filter = new VoiceFilter();
        $filter->setOrder('foo');
    }
}
