<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test\Message;

use DateTime;
use Exception;
use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use Vonage\Message\Message;

/**
 * Test that split messages allow access to all the underlying messages. The response from sending a message is the
 * only time a message may contain multiple 'parts'. When fetched from the API, each message is separate.
 *
 */
class FetchedMessageTest extends TestCase
{
    protected $to = '14845551212';
    protected $from = '16105551212';
    protected $text = 'this is test text';
    protected $set = ['to', 'from', 'text'];

    /**
     * @var Message
     */
    protected $message;

    public function setUp(): void
    {
        $this->message = new Message('02000000D912945A');
    }

    public function tearDown(): void
    {
        $this->message = null;
    }

    public function testCanAccessLastMessageAsArray(): void
    {
        @$this->message->setResponse($this->getResponse('search-outbound'));

        self::assertEquals('ACCEPTD', @$this->message['status']);
        self::assertEquals('02000000D912945A', @$this->message['message-id']);
        self::assertEquals('14845551212', @$this->message['to']);
        self::assertEquals('16105553980', @$this->message['from']);
        self::assertEquals('test with signature', @$this->message['body']);
        self::assertEquals('0.00570000', @$this->message['price']);
        self::assertEquals('2016-05-19 17:44:06', @$this->message['date-received']);
        self::assertEquals('1', @$this->message['error-code']);
        self::assertEquals('Unknown', @$this->message['error-code-label']);
        self::assertEquals('MT', @$this->message['type']);
    }

    /**
     * @throws Exception
     */
    public function testCanAccessLastMessageAsObject(): void
    {
        $date = new DateTime();
        $date->setDate(2016, 5, 19);
        $date->setTime(17, 44, 06);

        @$this->message->setResponse($this->getResponse('search-outbound'));

        self::assertEquals('ACCEPTD', $this->message->getDeliveryStatus());
        self::assertEquals('02000000D912945A', $this->message->getMessageId());
        self::assertEquals('14845551212', $this->message->getTo());
        self::assertEquals('16105553980', $this->message->getFrom());
        self::assertEquals('test with signature', $this->message->getBody());
        self::assertEquals('0.00570000', $this->message->getPrice());
        self::assertEquals($date, $this->message->getDateReceived());
        self::assertEquals('1', $this->message->getDeliveryError());
        self::assertEquals('Unknown', $this->message->getDeliveryLabel());
    }

    /**
     * Get the API response we'd expect for a call to the API. Message API currently returns 200 all the time, so only
     * change between success / fail is body of the message.
     *
     * @param string $type
     * @return Response
     */
    protected function getResponse(string $type = 'success'): Response
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'));
    }
}
