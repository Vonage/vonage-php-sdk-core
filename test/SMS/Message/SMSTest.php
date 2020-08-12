<?php
declare(strict_types=1);

namespace VonageTest\SMS\Message;

use InvalidArgumentException;
use Vonage\SMS\Message\SMS;
use PHPUnit\Framework\TestCase;

class SMSTest extends TestCase
{
    public function testSwitchesToUnicodeAutomatically()
    {
        $sms = new SMS('447700900000', '16105551212', 'こんにちは世界');

        $this->assertSame('unicode', $sms->getType());
    }

    public function testDeliveryCallbackCanBeSet()
    {
        $sms = new SMS('447700900000', '16105551212', 'Test Message');
        $sms->setDeliveryReceiptCallback('https://test.domain/webhooks/dlr');

        $this->assertSame('https://test.domain/webhooks/dlr', $sms->getDeliveryReceiptCallback());
        $this->assertSame(true, $sms->getRequestDeliveryReceipt());

        $data = $sms->toArray();
        $this->assertSame('https://test.domain/webhooks/dlr', $data['callback']);
        $this->assertSame(1, $data['status-report-req']);
    }

    public function testMessageClassCanBeSet()
    {
        $sms = new SMS('447700900000', '16105551212', 'Test Message');
        $sms->setMessageClass(0);

        $this->assertSame(0, $sms->getMessageClass());

        $data = $sms->toArray();
        $this->assertSame(0, $data['message-class']);
    }

    public function testInvalidMessageClassCannotBeSet()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Message Class must be 0-3');

        $sms = new SMS('447700900000', '16105551212', 'Test Message');
        $sms->setMessageClass(10);
    }

    public function testTTLCanBeSet()
    {
        $sms = new SMS('447700900000', '16105551212', 'Test Message');
        $sms->setTtl(40000);

        $this->assertSame(40000, $sms->getTtl());

        $data = $sms->toArray();
        $this->assertSame(40000, $data['ttl']);
    }

    public function testCannotSetInvalidTTL()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SMS TTL must be in the range of 20000-604800000 milliseconds');

        $sms = new SMS('447700900000', '16105551212', 'Test Message');
        $sms->setTtl(2);
    }

    public function testCannotSetTooLongOfaClientRef()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Client Ref can be no more than 40 characters');

        $sms = new SMS('447700900000', '16105551212', 'Test Message');
        $sms->setClientRef('This is a really long client ref and should throw an exception');
    }
}
