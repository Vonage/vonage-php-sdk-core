<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Message;

use PHPUnit\Framework\TestCase;

class MessageCreationTest extends TestCase
{
    protected $to   = '14845551212';
    protected $from = '16105551212';
    protected $text = 'this is test text';

    protected $set = array('to', 'from', 'text');

    /**
     * @var \Vonage\Message\Message
     */
    protected $message;

    public function setUp(): void
    {
        $this->message = new \Vonage\Message\Message($this->to, $this->from, [
            'text' => $this->text
        ]);
    }

    public function tearDown(): void
    {
        $this->message = null;
    }

    /**
     * Creating a new message, should result in the correct (matching) parameters.
     */
    public function testRequiredParams()
    {
        $params = @$this->message->getRequestData();

        $this->assertEquals($this->to,   $params['to']);
        $this->assertEquals($this->from, $params['from']);
    }

    /**
     * Optional params shouldn't be in the response, unless set.
     */
    public function testNoDefaultParams()
    {
        $params = array_keys(@$this->message->getRequestData());
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
        $params = @$this->message->getRequestData();
        $this->assertArrayNotHasKey($param, $params);

        //test values
        foreach($values as $value => $expected){
            $this->message->$setter($value);
            $params = @$this->message->getRequestData();
            $this->assertArrayHasKey($param, $params);
            $this->assertEquals($expected, $params[$param]);
        }
    }

    public function optionalParams()
    {
        return array(
            array('requestDLR',   'status-report-req', array(true => 1, false => 0)),
            array('setClientRef', 'client-ref',        array('test' => 'test')),
            array('setCallback',  'callback',          array('http://example.com/test-callback' => 'http://example.com/test-callback')),
            array('setNetwork',   'network-code',      array('test' => 'test')),
            array('setTTL',       'ttl',               array('1' => 1)),
            array('setClass',     'message-class',     array(\Vonage\Message\Text::CLASS_FLASH => \Vonage\Message\Text::CLASS_FLASH)),
        );
    }

    /**
     * Returns a series of methods/args to test on a Message object
     */
    public static function responseMethodChangeList()
    {
        return [
            ['requestDLR', true],
            ['setCallback', 'https://example.com/changed'],
            ['setClientRef', 'my-personal-message'],
            ['setNetwork', '1234'],
            ['setTTL', 3600],
            ['setClass', 0],
        ];
    }

    /**
     * Throw an exception when we make a call on a method that cannot change after request
     *
     * @dataProvider responseMethodChangeList
     */
    public function testCanNotChangeCreationAfterResponse($method, $argument)
    {
        $this->expectException('RuntimeException');

        $data = ['test' => 'test'];
        $response = new \Zend\Diactoros\Response();
        $response->getBody()->write(json_encode($data));
        @$this->message->setResponse($response);

        $this->message->$method($argument);
    }
}
