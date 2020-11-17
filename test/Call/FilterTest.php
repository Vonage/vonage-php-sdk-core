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

        $this->assertArrayHasKey('conversation_uuid', $query);
        $this->assertEquals('test', $query['conversation_uuid']);

        $this->filter->setConversation(new Conversation('test'));
        $query = $this->filter->getQuery();

        $this->assertArrayHasKey('conversation_uuid', $query);
        $this->assertEquals('test', $query['conversation_uuid']);
    }

    public function testStatus(): void
    {
        $this->filter->setStatus('test');
        $query = $this->filter->getQuery();

        $this->assertArrayHasKey('status', $query);
        $this->assertEquals('test', $query['status']);
    }

    public function testStart(): void
    {
        $date = new DateTime('2018-03-31 11:33:42+00:00');
        $this->filter->setStart($date);
        $query = $this->filter->getQuery();

        $this->assertArrayHasKey('date_start', $query);
        $this->assertEquals('2018-03-31T11:33:42Z', $query['date_start']);
    }

    public function testStartOtherTimezone(): void
    {
        $date = new DateTime('2018-03-31 11:33:42-03:00');
        $this->filter->setStart($date);
        $query = $this->filter->getQuery();

        $this->assertArrayHasKey('date_start', $query);
        $this->assertEquals('2018-03-31T14:33:42Z', $query['date_start']);
    }

    public function testEnd(): void
    {
        $date = new DateTime('2018-03-31 11:33:42+00:00');
        $this->filter->setEnd($date);
        $query = $this->filter->getQuery();

        $this->assertArrayHasKey('date_end', $query);
        $this->assertEquals('2018-03-31T11:33:42Z', $query['date_end']);
    }

    public function testEndOtherTimezone(): void
    {
        $date = new DateTime('2018-03-31 11:33:42+03:00');
        $this->filter->setEnd($date);
        $query = $this->filter->getQuery();

        $this->assertArrayHasKey('date_end', $query);
        $this->assertEquals('2018-03-31T08:33:42Z', $query['date_end']);
    }

    public function testSize(): void
    {
        $this->filter->setSize(1);
        $query = $this->filter->getQuery();

        $this->assertArrayHasKey('page_size', $query);
        $this->assertEquals(1, $query['page_size']);
    }

    public function testIndex(): void
    {
        $this->filter->setIndex(1);
        $query = $this->filter->getQuery();

        $this->assertArrayHasKey('record_index', $query);
        $this->assertEquals(1, $query['record_index']);
    }

    public function testOrder(): void
    {
        $this->filter->setOrder('asc');
        $query = $this->filter->getQuery();

        $this->assertArrayHasKey('order', $query);
        $this->assertEquals('asc', $query['order']);
    }

    public function testAsc(): void
    {
        $this->filter->sortAscending();
        $query = $this->filter->getQuery();

        $this->assertArrayHasKey('order', $query);
        $this->assertEquals('asc', $query['order']);
    }

    public function testDesc(): void
    {
        $this->filter->sortDescending();
        $query = $this->filter->getQuery();

        $this->assertArrayHasKey('order', $query);
        $this->assertEquals('desc', $query['order']);
    }
}
