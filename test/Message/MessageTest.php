<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace MexmoTest\Message;
use Zend\Diactoros\Response;

class MessageTest extends \PHPUnit_Framework_TestCase
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

    public function testRequestSetsData()
    {
        $data = ['test' => 'test'];
        $request = new \Zend\Diactoros\Request('http://example.com?' . http_build_query($data));
        $this->message->setRequest($request);

        $this->assertSame($request, $this->message->getRequest());
        $requestData = $this->message->getRequestData();
        $this->assertEquals($data, $requestData);
    }

    public function testResponseSetsData()
    {
        $data = ['test' => 'test'];
        $response = new \Zend\Diactoros\Response();
        $response->getBody()->write(json_encode($data));
        $response->getBody()->rewind();

        $this->message->setResponse($response);

        $this->assertSame($response, $this->message->getResponse());
        $this->assertEquals($data, $this->message->getResponseData());
    }

    /**
     * Common optional params can be set
     * @dataProvider responseSizes
     */
    public function testCanCountResponseMessages($size, $response = null)
    {
        if($response){
            $this->message->setResponse($response);
        }

        $this->assertCount($size, $this->message);
    }

    public function responseSizes()
    {
        return [
            [0, null],
            [1, $this->getResponse()],
            [3, $this->getResponse('multi')]
        ];
    }

    public function testCanAccessLastMessageAsArray()
    {
        $this->message->setResponse($this->getResponse('multi'));
        $this->assertEquals('0', $this->message['status']);
        $this->assertEquals('00000126', $this->message['message-id']);
        $this->assertEquals('44123456789', $this->message['to']);
        $this->assertEquals('1.00', $this->message['remaining-balance']);
        $this->assertEquals('0.05', $this->message['message-price']);
        $this->assertEquals('23410', $this->message['network']);
    }

    public function testCanAccessAnyMessageAsArray()
    {
        $this->message->setResponse($this->getResponse('multi'));
        $this->assertEquals('00000124', $this->message[0]['message-id']);
        $this->assertEquals('00000125', $this->message[1]['message-id']);
        $this->assertEquals('00000126', $this->message[2]['message-id']);
        $this->assertEquals('1.10', $this->message[0]['remaining-balance']);
        $this->assertEquals('1.05', $this->message[1]['remaining-balance']);
        $this->assertEquals('1.00', $this->message[2]['remaining-balance']);
    }

    public function testCanAccessLastMessageAsObject()
    {
        $this->message->setResponse($this->getResponse('multi'));
        $this->assertEquals('0', $this->message->getStatus());
        $this->assertEquals('00000126', $this->message->getId());
        $this->assertEquals('44123456789', $this->message->getTo());
        $this->assertEquals('1.00', $this->message->getRemainingBalance());
        $this->assertEquals('0.05', $this->message->getPrice());
        $this->assertEquals('23410', $this->message->getNetwork());
    }

    public function testCanAccessAnyMessagesAsObject()
    {
        $this->message->setResponse($this->getResponse('multi'));
        $this->assertEquals('00000124', $this->message->getId(0));
        $this->assertEquals('00000125', $this->message->getId(1));
        $this->assertEquals('00000126', $this->message->getId(2));
        $this->assertEquals('1.10', $this->message->getRemainingBalance(0));
        $this->assertEquals('1.05', $this->message->getRemainingBalance(1));
        $this->assertEquals('1.00', $this->message->getRemainingBalance(2));
    }

    public function testCanIterateOverMessageParts()
    {
        foreach($this->message as $index => $part){
            $this->fail('should not be able to iterate over empty message');
        }

        $this->message->setResponse($this->getResponse('multi'));

        $iterated = false;
        foreach($this->message as $index => $part){
            $iterated = true;
            $this->assertEquals('0', $part['status']);
            $this->assertEquals('44123456789', $part['to']);
            $this->assertEquals('23410', $part['network']);
            $this->assertEquals('0.05', $part['message-price']);

            switch($index){
                case 0:
                    $this->assertEquals('00000124', $part['message-id']);
                    $this->assertEquals('1.10', $part['remaining-balance']);
                    break;
                case 1:
                    $this->assertEquals('00000125', $part['message-id']);
                    $this->assertEquals('1.05', $part['remaining-balance']);
                    break;
                case 2:
                    $this->assertEquals('00000126', $part['message-id']);
                    $this->assertEquals('1.00', $part['remaining-balance']);
                    break;
            }
        }

        if(!$iterated){
            $this->fail('did not iterate over message with parts');
        }
    }

    public function testCanNotChangeRequestAfterResponse()
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

    /**
     * Get the API response we'd expect for a call to the API. Message API currently returns 200 all the time, so only
     * change between success / fail is body of the message.
     *
     * @param string $type
     * @return Response
     */
    protected function getResponse($type = 'success')
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'r'));
    }
}
