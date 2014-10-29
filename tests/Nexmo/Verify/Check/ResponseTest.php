<?php
/**
 * Created by PhpStorm.
 * User: tjlytle
 * Date: 10/28/14
 * Time: 10:58 PM
 */

namespace Nexmo\Verify\Check;


class ResponseTest extends \PHPUnit_Framework_TestCase
{
    protected $data = array(
        'event_id' => '1234abcd',
        'status' => '0',
        'price' => '.03',
        'currency' => 'EUR'
    );

    protected $response;

    public function setUp()
    {
        $this->response = new Response($this->data);
    }

    public function testResponseMap()
    {
        $this->assertEquals($this->data['event_id'], $this->response->getEventId());
        $this->assertEquals($this->data['status'], $this->response->getStatus());
        $this->assertEquals($this->data['price'], $this->response->getPrice());
        $this->assertEquals($this->data['currency'], $this->response->getCurrency());
    }
}
