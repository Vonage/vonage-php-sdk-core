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
class FetchedMessageTest extends \PHPUnit_Framework_TestCase
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
        $this->message = new \Nexmo\Message\Message('02000000D912945A');
    }

    public function tearDown()
    {
        $this->message = null;
    }

    public function testCanAccessLastMessageAsArray()
    {
        $this->message->setResponse($this->getResponse('search-outbound'));
        $this->assertEquals('ACCEPTD', $this->message['status']);
        $this->assertEquals('02000000D912945A', $this->message['message-id']);
        $this->assertEquals('14845551212', $this->message['to']);
        $this->assertEquals('16105553980', $this->message['from']);
        $this->assertEquals('test with signature', $this->message['body']);
        $this->assertEquals('0.00570000', $this->message['price']);
        $this->assertEquals('2016-05-19 17:44:06', $this->message['date-received']);
        $this->assertEquals('1', $this->message['error-code']);
        $this->assertEquals('Unknown', $this->message['error-code-label']);
        $this->assertEquals('MT', $this->message['type']);
    }

    public function testCanAccessLastMessageAsObject()
    {
        $date = new \DateTime();
        $date->setDate(2016, 5, 19);
        $date->setTime(17, 44, 06);

        $this->message->setResponse($this->getResponse('search-outbound'));

        $this->message->setResponse($this->getResponse('search-outbound'));
        $this->assertEquals('ACCEPTD', $this->message->getDeliveryStatus());
        $this->assertEquals('02000000D912945A', $this->message->getMessageId());
        $this->assertEquals('14845551212', $this->message->getTo());
        $this->assertEquals('16105553980', $this->message->getFrom());
        $this->assertEquals('test with signature', $this->message->getBody());
        $this->assertEquals('0.00570000', $this->message->getPrice());
        $this->assertEquals($date, $this->message->getDateReceived());
        $this->assertEquals('1', $this->message->getDeliveryError());
        $this->assertEquals('Unknown', $this->message->getDeliveryLabel());
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
