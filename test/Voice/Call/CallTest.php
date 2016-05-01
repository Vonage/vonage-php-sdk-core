<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */
namespace Nexmo\Voice\Call;
class CallTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Call
     */
    protected $call;

    protected $to = '15554443232';
    protected $from = '15551112323';
    protected $url = 'http://example.com';

    public function setUp()
    {
        $this->call = new Call($this->url, $this->to, $this->from);
    }

    public function testConstructParams()
    {
        $params = $this->call->getParams();

        $this->assertArrayHasKey('to', $params);
        $this->assertArrayHasKey('from', $params);
        $this->assertArrayHasKey('answer_url', $params);

        $this->assertEquals($this->to, $params['to']);
        $this->assertEquals($this->from, $params['from']);
        $this->assertEquals($this->url, $params['answer_url']);
    }

    public function testFromOptional()
    {
        $call = new Call($this->url, $this->to);

        $params = $call->getParams();

        $this->assertArrayNotHasKey('from', $params);
    }

    public function testMachine()
    {
        $this->call->setMachineDetection();
        $params = $this->call->getParams();
        $this->assertArrayHasKey('machine_detection', $params);
        $this->assertArrayNotHasKey('machine_timeout', $params);
        $this->assertEquals('hangup', $params['machine_detection']);

        $this->call->setMachineDetection(true, 100);
        $params = $this->call->getParams();
        $this->assertArrayHasKey('machine_detection', $params);
        $this->assertArrayHasKey('machine_timeout', $params);
        $this->assertEquals('hangup', $params['machine_detection']);
        $this->assertEquals(100, $params['machine_timeout']);

        $this->call->setMachineDetection(false);
        $params = $this->call->getParams();
        $this->assertArrayHasKey('machine_detection', $params);
        $this->assertArrayNotHasKey('machine_timeout', $params);
        $this->assertEquals('true', $params['machine_detection']);
    }

    /**
     * @dataProvider getCallbacks
     */
    public function testCallback($method, $param, $param_method)
    {
        $this->call->$method('http://example.com');
        $params = $this->call->getParams();
        $this->assertArrayHasKey($param, $params);
        $this->assertEquals('http://example.com', $params[$param]);
        $this->assertArrayNotHasKey($param_method, $params);

        $this->call->$method('http://example.com', 'POST');
        $params = $this->call->getParams();
        $this->assertArrayHasKey($param, $params);
        $this->assertEquals('http://example.com', $params[$param]);
        $this->assertArrayHasKey($param_method, $params);
        $this->assertEquals('POST', $params[$param_method]);

        $this->call->$method('http://example.com');
        $params = $this->call->getParams();
        $this->assertArrayHasKey($param, $params);
        $this->assertEquals('http://example.com', $params[$param]);
        $this->assertArrayNotHasKey($param_method, $params);
    }

    public function getCallbacks()
    {
        return array(
            array('setAnswer', 'answer_url', 'answer_method'),
            array('setError', 'error_url', 'error_method'),
            array('setStatus', 'status_url', 'status_method')
        );
    }
}
 