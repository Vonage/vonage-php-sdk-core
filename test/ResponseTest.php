<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

use Vonage\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    /**
     * @var Response
     */
    protected $response;

    protected $json = '{"message-count":"1","messages":[{"status":"returnCode","message-id":"messageId","to":"to","client-ref":"client-ref","remaining-balance":"remaining-balance","message-price":"message-price","network":"network","error-text":"error-message"}]}';
    protected $array;

    public function setUp(): void
    {
        $this->response = new Response($this->json);
        $this->array = json_decode($this->json, true);
    }

    public function testMessageCount()
    {
        $this->assertEquals($this->array['message-count'], $this->response->count());
        $this->assertEquals($this->response->count(), count($this->response));
        $this->assertEquals($this->response->count(), count($this->response->getMessages()));

        $count = 0;
        foreach($this->response as $message){
            $this->assertInstanceOf('Vonage\Response\Message', $message);
            $count++;
        }

        $this->assertEquals($this->response->count(), $count);
    }
}
