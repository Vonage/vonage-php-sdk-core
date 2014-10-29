<?php
/**
 * Created by PhpStorm.
 * User: tjlytle
 * Date: 10/28/14
 * Time: 10:57 PM
 */

namespace Nexmo\Verify;


class ResponseTest extends \PHPUnit_Framework_TestCase
{
    protected $data = array(
        'request_id' => '1234abc',
        'status' => '0'
    );

    protected $response;

    public function setUp()
    {
        $this->response = new Response($this->data);
    }

    public function testResponseMap()
    {
        $this->assertEquals($this->data['request_id'], $this->response->getId());
        $this->assertEquals($this->data['status'], $this->response->getStatus());
    }
}