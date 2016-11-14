<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Calls;


use Nexmo\Calls\Filter;
use Nexmo\Conversations\Conversation;

class FilterTest extends \PHPUnit_Framework_TestCase
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

        $this->filter->setConversation(new Conversation('test'));
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
        $date = new \DateTime();
        $this->filter->setStart($date);
        $query = $this->filter->getQuery();
        $this->assertArrayHasKey('date_start', $query);
        $this->assertEquals($date->format('c'), $query['date_start']);
    }

    public function testEnd()
    {
        $date = new \DateTime();
        $this->filter->setEnd($date);
        $query = $this->filter->getQuery();
        $this->assertArrayHasKey('date_end', $query);
        $this->assertEquals($date->format('c'), $query['date_end']);
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
