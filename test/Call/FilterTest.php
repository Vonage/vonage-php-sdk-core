<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Calls;

use Nexmo\Call\Filter;
use Nexmo\Conversations\Conversation;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    /**
     * @var Filter
     */
    protected $filter;

    public function setUp()
    {
        $this->filter = new Filter();
    }

    public function testConversation()
    {
        $this->filter->setConversation('test');
        $query = $this->filter->getQuery();
        $this->assertArrayHasKey('conversation_uuid', $query);
        $this->assertEquals('test', $query['conversation_uuid']);

        $conversation = new Conversation();
        $conversation->setId('test');
        $this->filter->setConversation($conversation);
        $query = $this->filter->getQuery();
        $this->assertArrayHasKey('conversation_uuid', $query);
        $this->assertEquals('test', $query['conversation_uuid']);

    }

    public function testStatus()
    {
        $this->filter->setStatus('test');
        $query = $this->filter->getQuery();
        $this->assertArrayHasKey('status', $query);
        $this->assertEquals('test', $query['status']);
    }

    public function testStart()
    {
        $date = new \DateTime('2018-03-31 11:33:42');
        $this->filter->setStart($date);
        $query = $this->filter->getQuery();
        $this->assertArrayHasKey('date_start', $query);
        $this->assertEquals('2018-03-31T11:33:42Z', $query['date_start']);
    }

    public function testStartOtherTimezone()
    {
        $date = new \DateTime('2018-03-31 11:33:42-03:00');
        $this->filter->setStart($date);
        $query = $this->filter->getQuery();
        $this->assertArrayHasKey('date_start', $query);
        $this->assertEquals('2018-03-31T14:33:42Z', $query['date_start']);
    }

    public function testEnd()
    {
        $date = new \DateTime('2018-03-31 11:33:42');
        $this->filter->setEnd($date);
        $query = $this->filter->getQuery();
        $this->assertArrayHasKey('date_end', $query);
        $this->assertEquals('2018-03-31T11:33:42Z', $query['date_end']);
    }

    public function testEndOtherTimezone()
    {
        $date = new \DateTime('2018-03-31 11:33:42+03:00');
        $this->filter->setEnd($date);
        $query = $this->filter->getQuery();
        $this->assertArrayHasKey('date_end', $query);
        $this->assertEquals('2018-03-31T08:33:42Z', $query['date_end']);
    }

    public function testSize()
    {
        $this->filter->setSize(1);
        $query = $this->filter->getQuery();
        $this->assertArrayHasKey('page_size', $query);
        $this->assertEquals(1, $query['page_size']);
    }

    public function testIndex()
    {
        $this->filter->setIndex(1);
        $query = $this->filter->getQuery();
        $this->assertArrayHasKey('record_index', $query);
        $this->assertEquals(1, $query['record_index']);
    }

    public function testOrder()
    {
        $this->filter->setOrder('asc');
        $query = $this->filter->getQuery();
        $this->assertArrayHasKey('order', $query);
        $this->assertEquals('asc', $query['order']);
    }

    public function testAsc()
    {
        $this->filter->sortAscending();
        $query = $this->filter->getQuery();
        $this->assertArrayHasKey('order', $query);
        $this->assertEquals('asc', $query['order']);
    }

    public function testDesc()
    {
        $this->filter->sortDescending();
        $query = $this->filter->getQuery();
        $this->assertArrayHasKey('order', $query);
        $this->assertEquals('desc', $query['order']);
    }
}
