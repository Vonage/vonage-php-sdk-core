<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Message;


class MessageCreationTest extends \PHPUnit_Framework_TestCase
{
    protected $to   = '14845551212';
    protected $from = '16105551212';
    protected $text = 'this is test text';

    protected $set = array('to', 'from', 'text');

    /**
     * @var \Nexmo\Message\Message
     */
    protected $message;

    public function setUp()
    {
        $this->message = new \Nexmo\Message\Message($this->to, $this->from, [
            'text' => $this->text
        ]);
    }

    public function tearDown()
    {
        $this->message = null;
    }

    /**
     * Creating a new message, should result in the correct (matching) parameters.
     */
    public function testRequiredParams()
    {
        $params = $this->message->getRequestData();

        $this->assertEquals($this->to,   $params['to']);
        $this->assertEquals($this->from, $params['from']);
    }

    /**
     * Optional params shouldn't be in the response, unless set.
     */
    public function testNoDefaultParams()
    {
        $params = array_keys($this->message->getRequestData());
        $diff = array_diff($params, $this->set); // should be no difference
        $this->assertEmpty($diff, 'message params contain unset values (could change default behaviour)');
    }

    /**
     * Common optional params can be set
     * @dataProvider optionalParams
     */
    public function testOptionalParams($setter, $param, $values)
    {
        //check no default value
        $params = $this->message->getRequestData();
        $this->assertArrayNotHasKey($param, $params);

        //test values
        foreach($values as $value => $expected){
            $this->message->$setter($value);
            $params = $this->message->getRequestData();
            $this->assertArrayHasKey($param, $params);
            $this->assertEquals($expected, $params[$param]);
        }
    }

    public function optionalParams()
    {
        return array(
            array('requestDLR',   'status-report-req', array(true => 1, false => 0)),
            array('setClientRef', 'client-ref',        array('test' => 'test')),
            array('setNetwork',   'network-code',      array('test' => 'test')),
            array('setTTL',       'ttl',               array('1' => 1)),
            array('setClass',     'message-class',     array(\Nexmo\Message\Text::CLASS_FLASH => \Nexmo\Message\Text::CLASS_FLASH)),
        );
    }

    public function testCanNotChangeCreationAfterResponse()
    {
        $data = ['test' => 'test'];
        $response = new \Zend\Diactoros\Response();
        $response->getBody()->write(json_encode($data));
        $this->message->setResponse($response);

        $methods = [
            'requestDlr' => true
        ];

        foreach($methods as $method => $arg){
            try{
                $this->message->$method($arg);
            } catch (\RuntimeException $e) {
                continue;
            }

            $this->fail('entity allowed request data to be set after response');
        }
    }
}
