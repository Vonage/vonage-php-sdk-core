<?php

declare(strict_types=1);

namespace VonageTest;

use InvalidArgumentException;
use VonageTest\VonageTestCase;
use Vonage\Response;
use Vonage\Response\Message;

use function json_decode;

class ResponseTest extends VonageTestCase
{
    /**
     * @var Response
     */
    protected $response;

    protected $json = '{
       "message-count":"1",
       "messages":[
          {
             "status":"returnCode",
             "message-id":"messageId",
             "to":"to",
             "client-ref":"client-ref",
             "remaining-balance":"remaining-balance",
             "message-price":"message-price",
             "network":"network",
             "error-text":"error-message"
          }
       ]
    }';

    protected $array;

    public function setUp(): void
    {
        $this->response = new Response($this->json);
        $this->array = json_decode($this->json, true);
    }

    public function testMessageCount(): void
    {
        $this->assertEquals($this->array['message-count'], $this->response->count());
        $this->assertCount($this->response->count(), $this->response);
        $this->assertCount($this->response->count(), $this->response->getMessages());

        $count = 0;

        foreach ($this->response as $message) {
            $this->assertInstanceOf(Message::class, $message);
            $count++;
        }

        $this->assertEquals($this->response->count(), $count);
    }

    public function testThrowExceptionWhenNonStringPassed()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected response data to be a string');

        new Response(4);
    }
}
