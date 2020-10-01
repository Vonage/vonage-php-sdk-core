<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Message;

use Vonage\Message\Message;
use Vonage\Message\Text;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
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

    public function testRequestSetsData()
    {
        $data = ['test' => 'test'];
        $request = new \Zend\Diactoros\Request('http://example.com?' . http_build_query($data));
        @$this->message->setRequest($request);

        $this->assertSame($request, @$this->message->getRequest());
        $requestData = @$this->message->getRequestData();
        $this->assertEquals($data, $requestData);
    }

    public function testResponseSetsData()
    {
        $data = ['test' => 'test'];
        $response = new \Zend\Diactoros\Response();
        $response->getBody()->write(json_encode($data));
        $response->getBody()->rewind();

        @$this->message->setResponse($response);

        $this->assertSame($response, @$this->message->getResponse());
        $this->assertEquals($data, @$this->message->getResponseData());
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
     * When creating a message, it should not auto-detect encoding by default
     * @dataProvider messageEncodingProvider
     */
    public function testDoesNotAutodetectByDefault($msg, $encoding)
    {
        $message = new Text('to', 'from', $msg);
        $this->assertFalse($message->isEncodingDetectionEnabled());
        $d = $message->getRequestData(false);
        $this->assertEquals($d['type'], 'text');
    }

    /**
     * When creating a message, it should not auto-detect encoding by default
     * @dataProvider messageEncodingProvider
     */
    public function testDoesAutodetectWhenEnabled($msg, $encoding)
    {
        $message = new Text('to', 'from', $msg);
        $message->enableEncodingDetection();
        $this->assertTrue($message->isEncodingDetectionEnabled());

        $d = $message->getRequestData(false);
        $this->assertEquals($d['type'], $encoding);
    }

    public function messageEncodingProvider() {

        $r = [];
        $r['text'] = ['Hello World', 'text'];
        $r['emoji'] = ['Testing 💪', 'unicode'];
        $r['kanji'] = ['漢字', 'unicode'];
        return $r;
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
