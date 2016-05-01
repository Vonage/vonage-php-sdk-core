<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
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