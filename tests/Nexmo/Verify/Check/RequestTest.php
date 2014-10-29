<?php
/**
 * Created by PhpStorm.
 * User: tjlytle
 * Date: 10/28/14
 * Time: 10:58 PM
 */

namespace Nexmo\Verify\Check;


class RequestTest extends \PHPUnit_Framework_TestCase
{
    protected $request_id = '1234abcd';
    protected $code = '123456';

    public function testOptionalValues()
    {
        $request = new Request($this->request_id, $this->code);
        $this->assertArrayNotHasKey('ip_address', $request->getParams());
    }

    public function testRequiredValues()
    {
        $request = new Request($this->request_id, $this->code);
        $params = $request->getParams();

        $this->assertArrayHasKey('request_id', $params);
        $this->assertArrayHasKey('code', $params);

        $this->assertEquals($this->request_id, $params['request_id']);
        $this->assertEquals($this->code, $params['code']);
    }

    public function testIpAddress()
    {
        $request = new Request($this->request_id, $this->code, '127.0.0.1');
        $params = $request->getParams();

        $this->assertArrayHasKey('ip_address', $params);
        $this->assertEquals('127.0.0.1', $params['ip_address']);
    }
}
