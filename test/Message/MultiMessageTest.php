<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Message;

use Exception;
use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use Vonage\Message\Message;

/**
 * Test that split messages allow access to all the underlying messages. The response from sending a message is the
 * only time a message may contain multiple 'parts'. When fetched from the API, each message is separate.
 *
 */
class MultiMessageTest extends TestCase
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
        $this->message = new Message($this->to, $this->from, [
            'text' => $this->text
        ]);
    }

    public function tearDown(): void
    {
        $this->message = null;
    }

    /**
     * Common optional params can be set
     *
     * @dataProvider responseSizes
     * @param $size
     * @param null $response
     */
    public function testCanCountResponseMessages($size, $response = null): void
    {
        if ($response) {
            @$this->message->setResponse($response);
        }

        self::assertCount($size, $this->message);
    }

    /**
     * @return array[]
     */
    public function responseSizes(): array
    {
        return [
            [0, null],
            [1, $this->getResponse()],
            [3, $this->getResponse('multi')]
        ];
    }

    public function testCanAccessLastMessageAsArray(): void
    {
        @$this->message->setResponse($this->getResponse('multi'));

        self::assertEquals('0', @$this->message['status']);
        self::assertEquals('00000126', @$this->message['message-id']);
        self::assertEquals('44123456789', @$this->message['to']);
        self::assertEquals('1.00', @$this->message['remaining-balance']);
        self::assertEquals('0.05', @$this->message['message-price']);
        self::assertEquals('23410', @$this->message['network']);
    }

    public function testCanAccessAnyMessageAsArray(): void
    {
        @$this->message->setResponse($this->getResponse('multi'));

        self::assertEquals('00000124', @$this->message[0]['message-id']);
        self::assertEquals('00000125', @$this->message[1]['message-id']);
        self::assertEquals('00000126', @$this->message[2]['message-id']);
        self::assertEquals('1.10', @$this->message[0]['remaining-balance']);
        self::assertEquals('1.05', @$this->message[1]['remaining-balance']);
        self::assertEquals('1.00', @$this->message[2]['remaining-balance']);
    }

    /**
     * @throws Exception
     */
    public function testCanAccessLastMessageAsObject(): void
    {
        @$this->message->setResponse($this->getResponse('multi'));

        self::assertEquals('0', $this->message->getStatus());
        self::assertEquals('00000126', $this->message->getMessageId());
        self::assertEquals('44123456789', $this->message->getTo());
        self::assertEquals('1.00', $this->message->getRemainingBalance());
        self::assertEquals('0.05', $this->message->getPrice());
        self::assertEquals('23410', $this->message->getNetwork());
    }

    /**
     * @throws Exception
     */
    public function testCanAccessAnyMessagesAsObject(): void
    {
        @$this->message->setResponse($this->getResponse('multi'));

        self::assertEquals('00000124', $this->message->getMessageId(0));
        self::assertEquals('00000125', $this->message->getMessageId(1));
        self::assertEquals('00000126', $this->message->getMessageId(2));
        self::assertEquals('1.10', $this->message->getRemainingBalance(0));
        self::assertEquals('1.05', $this->message->getRemainingBalance(1));
        self::assertEquals('1.00', $this->message->getRemainingBalance(2));
    }

    public function testCanIterateOverMessageParts(): void
    {
        foreach ($this->message as $index => $part) {
            self::fail('should not be able to iterate over empty message');
        }

        @$this->message->setResponse($this->getResponse('multi'));

        $iterated = false;
        foreach ($this->message as $index => $part) {
            $iterated = true;
            self::assertEquals('0', $part['status']);
            self::assertEquals('44123456789', $part['to']);
            self::assertEquals('23410', $part['network']);
            self::assertEquals('0.05', $part['message-price']);

            switch ($index) {
                case 0:
                    self::assertEquals('00000124', $part['message-id']);
                    self::assertEquals('1.10', $part['remaining-balance']);
                    break;
                case 1:
                    self::assertEquals('00000125', $part['message-id']);
                    self::assertEquals('1.05', $part['remaining-balance']);
                    break;
                case 2:
                    self::assertEquals('00000126', $part['message-id']);
                    self::assertEquals('1.00', $part['remaining-balance']);
                    break;
            }
        }

        if (!$iterated) {
            self::fail('did not iterate over message with parts');
        }
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
