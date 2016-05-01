<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

use Nexmo\Response\Message;

class MessageTest extends PHPUnit_Framework_TestCase
{
    protected $message;

    public function testSuccess()
    {
        $json = '{"status":"0","message-id":"00000123","to":"44123456789","remaining-balance":"1.10","message-price":"0.05","network":"23410"}';

        $this->message = new Message(json_decode($json, true)); //response already has decoded

        $this->assertEquals(0, $this->message->getStatus());
        $this->assertEquals('00000123', $this->message->getId());
        $this->assertEquals('44123456789', $this->message->getTo());
        $this->assertEquals('1.10', $this->message->getBalance());
        $this->assertEquals('0.05', $this->message->getPrice());
        $this->assertEquals('23410', $this->message->getNetwork());

        $this->assertEmpty($this->message->getErrorMessage());
    }

    public function testFail()
    {
        $json = '{"status":"2","error-text":"Missing from param"}';
        $this->message = new Message(json_decode($json, true)); //response already has decoded

        $this->assertEquals(2, $this->message->getStatus());
        $this->assertEquals('Missing from param', $this->message->getErrorMessage());

        foreach(array('getId', 'getTo', 'getBalance', 'getPrice', 'getNetwork') as $getter){
            try{
                $this->message->$getter();
                $this->testFail('Trying to access ' . $getter . ' should have caused an exception');
            } catch (\RuntimeException $e) {}
        }
    }
}
