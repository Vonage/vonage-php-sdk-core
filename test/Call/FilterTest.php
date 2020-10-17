<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace VonageTest\Call;

use DateTime;
use PHPUnit\Framework\TestCase;
use Vonage\Call\Filter;
use Vonage\Conversations\Conversation;

class FilterTest extends TestCase
{
    /**
     * @var Filter
     */
    protected $filter;

    public function setUp(): void
    {
        $this->filter = @new Filter();
    }

    public function testConversation(): void
    {
        $this->filter->setConversation('test');
        $query = $this->filter->getQuery();

        self::assertArrayHasKey('conversation_uuid', $query);
        self::assertEquals('test', $query['conversation_uuid']);

        $this->filter->setConversation(new Conversation('test'));
        $query = $this->filter->getQuery();

        self::assertArrayHasKey('conversation_uuid', $query);
        self::assertEquals('test', $query['conversation_uuid']);
    }

    public function testStatus(): void
    {
        $this->filter->setStatus('test');
        $query = $this->filter->getQuery();

        self::assertArrayHasKey('status', $query);
        self::assertEquals('test', $query['status']);
    }

    public function testStart(): void
    {
        $date = new DateTime('2018-03-31 11:33:42+00:00');
        $this->filter->setStart($date);
        $query = $this->filter->getQuery();

        self::assertArrayHasKey('date_start', $query);
        self::assertEquals('2018-03-31T11:33:42Z', $query['date_start']);
    }

    public function testStartOtherTimezone(): void
    {
        $date = new DateTime('2018-03-31 11:33:42-03:00');
        $this->filter->setStart($date);
        $query = $this->filter->getQuery();

        self::assertArrayHasKey('date_start', $query);
        self::assertEquals('2018-03-31T14:33:42Z', $query['date_start']);
    }

    public function testEnd(): void
    {
        $date = new DateTime('2018-03-31 11:33:42+00:00');
        $this->filter->setEnd($date);
        $query = $this->filter->getQuery();

        self::assertArrayHasKey('date_end', $query);
        self::assertEquals('2018-03-31T11:33:42Z', $query['date_end']);
    }

    public function testEndOtherTimezone(): void
    {
        $date = new DateTime('2018-03-31 11:33:42+03:00');
        $this->filter->setEnd($date);
        $query = $this->filter->getQuery();

        self::assertArrayHasKey('date_end', $query);
        self::assertEquals('2018-03-31T08:33:42Z', $query['date_end']);
    }

    public function testSize(): void
    {
        $this->filter->setSize(1);
        $query = $this->filter->getQuery();

        self::assertArrayHasKey('page_size', $query);
        self::assertEquals(1, $query['page_size']);
    }

    public function testIndex(): void
    {
        $this->filter->setIndex(1);
        $query = $this->filter->getQuery();

        self::assertArrayHasKey('record_index', $query);
        self::assertEquals(1, $query['record_index']);
    }

    public function testOrder(): void
    {
        $this->filter->setOrder('asc');
        $query = $this->filter->getQuery();

        self::assertArrayHasKey('order', $query);
        self::assertEquals('asc', $query['order']);
    }

    public function testAsc(): void
    {
        $this->filter->sortAscending();
        $query = $this->filter->getQuery();

        self::assertArrayHasKey('order', $query);
        self::assertEquals('asc', $query['order']);
    }

    public function testDesc(): void
    {
        $this->filter->sortDescending();
        $query = $this->filter->getQuery();

        self::assertArrayHasKey('order', $query);
        self::assertEquals('desc', $query['order']);
    }
}
