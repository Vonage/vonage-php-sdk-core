<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Message;

use Nexmo\Message\InboundMessage;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class InboundMessageTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructionWithId()
    {
        $message = new InboundMessage('test1234');
        $this->assertSame('test1234', $message->getMessageId());
    }

    /**
     * Inbound messages can be created from a PSR-7 server request.
     * @dataProvider getRequests
     */
    public function testCanCreateWithServerRequest($request)
    {
        $message = new InboundMessage($request);
    }

    public function testCanCheckValid()
    {
        $request = $this->getServerRequest();
        $message = new InboundMessage($request);
        
        $this->assertTrue($message->isValid());
        
        $request = $this->getServerRequest('http://example.com', 'GET', 'invalid');
        $message = new InboundMessage($request);

        $this->assertFalse($message->isValid());
    }
    
    /**
     * Can access expected params via getters.
     * @dataProvider getRequests
     */
    public function testRequestObjectAccess($request)
    {
        $message = new InboundMessage($request);

        $this->assertEquals('14845552121', $message->getFrom());
        $this->assertEquals('16105553939', $message->getTo());
        $this->assertEquals('02000000DA7C52E7', $message->getMessageId());
        $this->assertEquals('Test this.', $message->getBody());
        $this->assertEquals('text', $message->getType());
    }


    /**
     * Can access raw params via array access.
     * @dataProvider getRequests
     */
    public function testRequestArrayAccess($request)
    {
        $message = new InboundMessage($request);

        $this->assertEquals('14845552121', $message['msisdn']);
        $this->assertEquals('16105553939', $message['to']);
        $this->assertEquals('02000000DA7C52E7', $message['messageId']);
        $this->assertEquals('Test this.', $message['text']);
        $this->assertEquals('text', $message['type']);
    }

    /**
     * Can access expected params when populated from an API request.
     * @dataProvider getResponses
     */
    public function testResponseObjectAccess($response)
    {
        $message = new InboundMessage('02000000DA7C52E7');
        $message->setResponse($response);

        $this->assertEquals('14845552121', $message->getFrom());
        $this->assertEquals('16105553939', $message->getTo());
        $this->assertEquals('02000000DA7C52E7', $message->getMessageId());
        $this->assertEquals('Test this.', $message->getBody());
        $this->assertEquals('6cff3913', $message->getAccountId());
        $this->assertEquals('US-VIRTUAL-BANDWIDTH', $message->getNetwork());
    }

    /**
     * Can access raw params when populated from an API request.
     * @dataProvider getResponses
     */
    public function testResponseArrayAccess($response)
    {
        $message = new InboundMessage('02000000DA7C52E7');
        $message->setResponse($response);

        $this->assertEquals('14845552121', $message['from']);
        $this->assertEquals('16105553939', $message['to']);
        $this->assertEquals('02000000DA7C52E7', $message['message-id']);
        $this->assertEquals('Test this.', $message['body']);
        $this->assertEquals('MO', $message['type']);
        $this->assertEquals('6cff3913', $message['account-id']);
        $this->assertEquals('US-VIRTUAL-BANDWIDTH', $message['network']);
    }

    public function testCanCreateReply()
    {
        $message = new InboundMessage($this->getServerRequest());

        $reply = $message->createReply('this is a reply');
        $this->assertInstanceOf('Nexmo\Message\Message', $reply);

        $params = $reply->getRequestData(false);

        $this->assertEquals('14845552121', $params['to']);
        $this->assertEquals('16105553939', $params['from']);
        $this->assertEquals('this is a reply', $params['text']);
    }

    public function getResponses()
    {
        return [
            [$this->getResponse('search-inbound')]
        ];
    }

    public function getRequests()
    {
        return [
            [$this->getServerRequest('https://ohyt2ctr9l0z.runscope.net/sms_post', 'POST', 'inbound')],
            [$this->getServerRequest('https://ohyt2ctr9l0z.runscope.net/sms_post', 'GET',  'inbound')],
        ];
    }

    /**
     * @param $url
     * @param string $method
     * @param null $file
     * @return ServerRequest
     */
    protected function getServerRequest($url = 'https://ohyt2ctr9l0z.runscope.net/sms_post', $method = 'GET', $type = 'inbound')
    {
        $data = file_get_contents(__DIR__ . '/requests/' . $type . '.txt');
        $params = [];
        parse_str($data, $params);

        $query = [];
        $parsed = null;

        switch(strtoupper($method)){
            case 'GET';
                $query = $params;
                $body = 'php://memory';
                break;
            default:
                $body = fopen(__DIR__ . '/requests/' . $type . '.txt', 'r');
                $query = [];
                $parsed = $params;
                break;
        }

        return new ServerRequest([], [], $url, $method, $body, [], [], $query, $parsed);
    }

    /**
     * Get the API response we'd expect for a call to the API.
     *
     * @param string $type
     * @return Response
     */
    protected function getResponse($type = 'success')
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'r'));
    }

}
