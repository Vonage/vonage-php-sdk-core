<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Response;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Vonage\Response\Message;

use function json_decode;

class MessageTest extends TestCase
{
    protected $message;

    public function testSuccess(): void
    {
        $json = '{
           "status":"0",
           "message-id":"00000123",
           "to":"44123456789",
           "remaining-balance":"1.10",
           "message-price":"0.05",
           "network":"23410"
        }';

        $this->message = new Message(json_decode($json, true)); //response already has decoded

        $this->assertEquals(0, $this->message->getStatus());
        $this->assertEquals('00000123', $this->message->getId());
        $this->assertEquals('44123456789', $this->message->getTo());
        $this->assertEquals('1.10', $this->message->getBalance());
        $this->assertEquals('0.05', $this->message->getPrice());
        $this->assertEquals('23410', $this->message->getNetwork());
        $this->assertEmpty($this->message->getErrorMessage());
    }

    public function testFail(): void
    {
        $json = '{
           "status":"2",
           "error-text":"Missing from param"
        }';

        $this->message = new Message(json_decode($json, true)); //response already has decoded

        $this->assertEquals(2, $this->message->getStatus());
        $this->assertEquals('Missing from param', $this->message->getErrorMessage());

        foreach (['getId', 'getTo', 'getBalance', 'getPrice', 'getNetwork'] as $getter) {
            try {
                $this->message->$getter();

                self::fail('Trying to access ' . $getter . ' should have caused an exception');
            } catch (RuntimeException $e) {
            }
        }
    }
}
