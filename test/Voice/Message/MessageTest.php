<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Voice\Message;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Message
     */
    protected $message;

    protected $text = 'TTS Text';
    protected $to   = '15553331212';
    protected $from = '15554441212';

    public function setUp()
    {
        $this->message = new Message($this->text, $this->to, $this->from);
    }

    public function testConstructorParams()
    {
        $params = $this->message->getParams();

        $this->assertArrayHasKey('text', $params);
        $this->assertArrayHasKey('to', $params);
        $this->assertArrayHasKey('from', $params);

        $this->assertEquals($this->text, $params['text']);
        $this->assertEquals($this->to, $params['to']);
        $this->assertEquals($this->from, $params['from']);
    }

    public function testFromIsOptional()
    {
        $message = new Message($this->text, $this->to);

        $params = $message->getParams();
        $this->assertArrayNotHasKey('from', $params);
    }

    public function testCallback()
    {
        $this->message->setCallback('http://example.com');
        $params = $this->message->getParams();
        $this->assertArrayHasKey('callback', $params);
        $this->assertEquals('http://example.com', $params['callback']);
        $this->assertArrayNotHasKey('callback_method', $params);

        $this->message->setCallback('http://example.com', 'POST');
        $params = $this->message->getParams();
        $this->assertArrayHasKey('callback', $params);
        $this->assertEquals('http://example.com', $params['callback']);
        $this->assertArrayHasKey('callback_method', $params);
        $this->assertEquals('POST', $params['callback_method']);

        $this->message->setCallback('http://example.com');
        $params = $this->message->getParams();
        $this->assertArrayHasKey('callback', $params);
        $this->assertEquals('http://example.com', $params['callback']);
        $this->assertArrayNotHasKey('callback_method', $params);
    }

    public function testMachine()
    {
        $this->message->setMachineDetection();
        $params = $this->message->getParams();
        $this->assertArrayHasKey('machine_detection', $params);
        $this->assertArrayNotHasKey('machine_timeout', $params);
        $this->assertEquals('hangup', $params['machine_detection']);

        $this->message->setMachineDetection(true, 100);
        $params = $this->message->getParams();
        $this->assertArrayHasKey('machine_detection', $params);
        $this->assertArrayHasKey('machine_timeout', $params);
        $this->assertEquals('hangup', $params['machine_detection']);
        $this->assertEquals(100, $params['machine_timeout']);

        $this->message->setMachineDetection(false);
        $params = $this->message->getParams();
        $this->assertArrayHasKey('machine_detection', $params);
        $this->assertArrayNotHasKey('machine_timeout', $params);
        $this->assertEquals('true', $params['machine_detection']);
    }

    /**
     * @dataProvider optionalParams
     */
    public function testOptionalParams($setter, $param, $values)
    {
        //check no default value
        $params = $this->message->getParams();
        $this->assertArrayNotHasKey($param, $params);

        //test values
        foreach($values as $value => $expected){
            $this->message->$setter($value);
            $params = $this->message->getParams();
            $this->assertArrayHasKey($param, $params);
            $this->assertEquals($expected, $params[$param]);
        }
    }

    public function optionalParams()
    {
        return array(
            array('setLanguage',  'lg',            array('test' => 'test')),
            array('setVoice',     'voice',         array('test' => 'test')),
            array('setRepeat',    'repeat',        array(2 => 2)),
        );
    }

}
 