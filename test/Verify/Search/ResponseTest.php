<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Verify\Search;


class ResponseTest extends \PHPUnit_Framework_TestCase
{
    protected $data = array(
        'request_id' => '1234abcd',
        'account_id' => 'abcd1234',
        'number' => '14845551212',
        'sender_id' => '18123339090',
        'date_submitted' => '2014-10-29 10:10:10',
        'date_finalized' => '2014-10-29 11:11:11',
        'first_event_date' => '2014-10-29 10:10:10',
        'last_event_date' => '2014-10-29 11:11:11',
        'price' => '0.03',
        'currency' => 'EUR',
        'status' => 'SUCCESS',
        'checks' => "[{'date_received':'2014-10-29 04:05:56','code':'3550','status':'VALID','ip_address':''}]"
    );

    protected $response;

    public function setUp()
    {
        $this->response = new Response($this->data);
    }

    public function testResponseMap()
    {
        $this->assertEquals($this->data['status'], $this->response->getStatus());
        $this->assertEquals($this->data['request_id'], $this->response->getId());
    }
}
