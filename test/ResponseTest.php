<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test;

use PHPUnit\Framework\TestCase;
use Vonage\Response;
use Vonage\Response\Message;

class ResponseTest extends TestCase
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
        self::assertEquals($this->array['message-count'], $this->response->count());
        self::assertCount($this->response->count(), $this->response);
        self::assertCount($this->response->count(), $this->response->getMessages());

        $count = 0;

        foreach ($this->response as $message) {
            self::assertInstanceOf(Message::class, $message);
            $count++;
        }

        self::assertEquals($this->response->count(), $count);
    }
}
