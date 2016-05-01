<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Message\Callback;


class ReceiptTest extends \PHPUnit_Framework_TestCase
{
    protected $data = array(
        'err-code' => '0',
        'message-timestamp' => '2014-07-23 03:41:03',
        'messageId' => '0300000049CE26E1',
        'msisdn' => '15553217878',
        'network-code' => '310260',
        'price' => '0.00480000',
        'scts' => '1412301225',
        'status' => 'accepted',
        'to' => '15673332121',
        //'timestamp' => '1406086863'
    );

    /**
     * @var Receipt
     */
    protected $receipt;

    public function setUp()
    {
        $this->receipt = new Receipt($this->data);
    }

    public function testServiceCenterTimestamp()
    {
        $date = $this->receipt->getTimestamp();
        $this->assertEquals(new \DateTime('12/30/2014 12:25'), $date);
    }

    public function testSentTimestamp()
    {
        $date = $this->receipt->getSent();
        $this->assertEquals(new \DateTime('7/23/2014 03:41:03'), $date);
    }

    public function testSimpleValues()
    {
        $this->assertEquals($this->data['err-code'], $this->receipt->getErrorCode());
        $this->assertEquals($this->data['messageId'], $this->receipt->getId());
        $this->assertEquals($this->data['network-code'], $this->receipt->getNetwork());
        $this->assertEquals($this->data['price'], $this->receipt->getPrice());
        $this->assertEquals($this->data['status'], $this->receipt->getStatus());

        $this->assertEquals($this->data['msisdn'], $this->receipt->getReceiptFrom());
        $this->assertEquals($this->data['msisdn'], $this->receipt->getTo());

        $this->assertEquals($this->data['to'], $this->receipt->getReceiptTo());
        $this->assertEquals($this->data['to'], $this->receipt->getFrom());
    }

    public function testClientRefDefault()
    {
        $this->assertNull($this->receipt->getClientRef());
    }

    public function testClientRef()
    {
        $receipt = new Receipt(array_merge(array('client-ref' => 'test'), $this->data));
        $this->assertEquals('test', $receipt->getClientRef());
    }

}
 