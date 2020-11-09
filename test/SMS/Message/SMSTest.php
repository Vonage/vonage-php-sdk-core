<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\SMS\Message;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Vonage\SMS\Message\SMS;

class SMSTest extends TestCase
{
    public function testSwitchesToUnicodeAutomatically(): void
    {
        $this->assertSame(
            'unicode',
            (new SMS('447700900000', '16105551212', 'こんにちは世界'))
                ->getType()
        );
    }

    public function testDeliveryCallbackCanBeSet(): void
    {
        $sms = (new SMS('447700900000', '16105551212', 'Test Message'))
            ->setDeliveryReceiptCallback('https://test.domain/webhooks/dlr');

        $this->assertSame('https://test.domain/webhooks/dlr', $sms->getDeliveryReceiptCallback());
        $this->assertTrue($sms->getRequestDeliveryReceipt());

        $data = $sms->toArray();

        $this->assertSame('https://test.domain/webhooks/dlr', $data['callback']);
        $this->assertSame(1, $data['status-report-req']);
    }

    public function testMessageClassCanBeSet(): void
    {
        $sms = (new SMS('447700900000', '16105551212', 'Test Message'))
            ->setMessageClass(0);

        $this->assertSame(0, $sms->getMessageClass());

        $data = $sms->toArray();

        $this->assertSame(0, $data['message-class']);
    }

    public function testInvalidMessageClassCannotBeSet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Message Class must be 0-3');

        (new SMS('447700900000', '16105551212', 'Test Message'))
            ->setMessageClass(10);
    }

    public function testTTLCanBeSet(): void
    {
        $sms = (new SMS('447700900000', '16105551212', 'Test Message'))
            ->setTtl(40000);

        $this->assertSame(40000, $sms->getTtl());

        $data = $sms->toArray();

        $this->assertSame(40000, $data['ttl']);
    }

    public function testCannotSetInvalidTTL(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SMS TTL must be in the range of 20000-604800000 milliseconds');

        (new SMS('447700900000', '16105551212', 'Test Message'))
            ->setTtl(2);
    }

    public function testCannotSetTooLongOfaClientRef(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Client Ref can be no more than 40 characters');

        (new SMS('447700900000', '16105551212', 'Test Message'))
            ->setClientRef('This is a really long client ref and should throw an exception');
    }
}
