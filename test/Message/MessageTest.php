<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Message;
use Nexmo\Message\Message;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

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
     * For getting message data from API, can create a simple object with just an ID.
     */
    public function testCanCreateWithId()
    {
        $message = new Message('00000123');
        $this->assertEquals('00000123', $message->getMessageId());
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
