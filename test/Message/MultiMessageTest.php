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

/**
 * Test that split messages allow access to all the underlying messages. The response from sending a message is the
 * only time a message may contain multiple 'parts'. When fetched from the API, each message is separate.
 *
 */
class MultiMessageTest extends \PHPUnit_Framework_TestCase
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
        $this->assertEquals('00000126', $this->message->getMessageId());
        $this->assertEquals('44123456789', $this->message->getTo());
        $this->assertEquals('1.00', $this->message->getRemainingBalance());
        $this->assertEquals('0.05', $this->message->getPrice());
        $this->assertEquals('23410', $this->message->getNetwork());
    }

    public function testCanAccessAnyMessagesAsObject()
    {
        $this->message->setResponse($this->getResponse('multi'));
        $this->assertEquals('00000124', $this->message->getMessageId(0));
        $this->assertEquals('00000125', $this->message->getMessageId(1));
        $this->assertEquals('00000126', $this->message->getMessageId(2));
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
