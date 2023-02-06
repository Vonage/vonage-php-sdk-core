<?php

declare(strict_types=1);

namespace VonageTest\SMS\Message;

use InvalidArgumentException;
use VonageTest\VonageTestCase;
use Vonage\SMS\Message\SMS;

class SMSTest extends VonageTestCase
{
    public function testCanSetUnicodeType(): void
    {
        $sms = (new SMS('447700900000', '16105551212', 'Test Message'));
        $this->assertSame('text', $sms->getType());
        $sms->setType('unicode');
        $this->assertSame('unicode', $sms->getType());
    }

    public function testCanSetUnicodeTypeInConstructor(): void
    {
        $sms = (new SMS('447700900000', '16105551212', 'Test Message', 'unicode'));
        $this->assertSame('unicode', $sms->getType());
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

    public function testCanSetEntityId(): void
    {
        $sms = new SMS('447700900000', '16105551212', 'Test Message');
        $sms->setEntityId('abcd');

        $expected = [
            'text' => 'Test Message',
            'entity-id' => 'abcd',
            'to' => '447700900000',
            'from' => '16105551212',
            'type' => 'text',
            'ttl' => 259200000,
            'status-report-req' => 1,
        ];

        $this->assertSame($expected, $sms->toArray());
        $this->assertSame($expected['entity-id'], $sms->getEntityId());
    }

    public function testCanSetContentId(): void
    {
        $sms = new SMS('447700900000', '16105551212', 'Test Message');
        $sms->setContentId('1234');

        $expected = [
            'text' => 'Test Message',
            'content-id' => '1234',
            'to' => '447700900000',
            'from' => '16105551212',
            'type' => 'text',
            'ttl' => 259200000,
            'status-report-req' => 1,
        ];

        $this->assertSame($expected, $sms->toArray());
        $this->assertSame($expected['content-id'], $sms->getContentId());
    }

    public function testDLTInfoAppearsInRequest(): void
    {
        $sms = new SMS('447700900000', '16105551212', 'Test Message');
        $sms->enableDLT('abcd', '1234');

        $expected = [
            'text' => 'Test Message',
            'entity-id' => 'abcd',
            'content-id' => '1234',
            'to' => '447700900000',
            'from' => '16105551212',
            'type' => 'text',
            'ttl' => 259200000,
            'status-report-req' => 1,
        ];

        $this->assertSame($expected, $sms->toArray());
    }

    public function testDLTInfoDoesNotAppearsWhenNotSet(): void
    {
        $sms = new SMS('447700900000', '16105551212', 'Test Message');

        $expected = [
            'text' => 'Test Message',
            'to' => '447700900000',
            'from' => '16105551212',
            'type' => 'text',
            'ttl' => 259200000,
            'status-report-req' => 1,
        ];

        $this->assertSame($expected, $sms->toArray());
    }

    /**
     * @dataProvider unicodeStringDataProvider
     * @return void
     */
    public function testGsm7Identification(string $message, bool $expectedGsm7)
    {
        $sms = new SMS('16105551212', '16105551212', $message);
        $this->assertEquals($expectedGsm7, $sms->isGsm7());
    }

    public function unicodeStringDataProvider(): array
    {
        return [
            ['this is a text', true],
            ['This is also a GSM7 text', true],
            ['This is a Çotcha', true],
            ['This is also a çotcha', true],
            ['日本語でボナージュ', false],
        ];
    }
}
