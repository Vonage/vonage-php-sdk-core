<?php

declare(strict_types=1);

namespace VonageTest\Verify;

use InvalidArgumentException;
use Vonage\Verify\StartVerification;
use VonageTest\VonageTestCase;

class StartVerificationTest extends VonageTestCase
{
    public function testConstructorSetsRequiredFields(): void
    {
        $request = new StartVerification('14845551212', 'MyBrand');

        $this->assertSame('14845551212', $request->getNumber());
        $this->assertSame('MyBrand', $request->getBrand());
        $this->assertSame(StartVerification::WORKFLOW_SMS_TTS_TSS, $request->getWorkflowId());
    }

    public function testConstructorSetsWorkflowId(): void
    {
        $request = new StartVerification('14845551212', 'MyBrand', StartVerification::WORKFLOW_SMS);

        $this->assertSame(StartVerification::WORKFLOW_SMS, $request->getWorkflowId());
    }

    public function testDefaultValues(): void
    {
        $request = new StartVerification('14845551212', 'MyBrand');

        $this->assertSame('VONAGE', $request->getSenderId());
        $this->assertSame(StartVerification::PIN_LENGTH_4, $request->getCodeLength());
        $this->assertSame('', $request->getCountry());
        $this->assertSame('', $request->getLocale());
        $this->assertSame(300, $request->getPinExpiry());
        $this->assertSame(300, $request->getNextEventWait());
    }

    public function testSettersReturnStatic(): void
    {
        $request = new StartVerification('14845551212', 'MyBrand');

        $this->assertSame($request, $request->setCountry('US'));
        $this->assertSame($request, $request->setSenderId('ACME'));
        $this->assertSame($request, $request->setCodeLength(StartVerification::PIN_LENGTH_6));
        $this->assertSame($request, $request->setLocale('en-us'));
        $this->assertSame($request, $request->setPinExpiry(120));
        $this->assertSame($request, $request->setNextEventWait(120));
        $this->assertSame($request, $request->setWorkflowId(StartVerification::WORKFLOW_TTS));
    }

    public function testToArrayContainsRequiredFields(): void
    {
        $request = new StartVerification('14845551212', 'MyBrand');
        $data = $request->toArray();

        $this->assertSame('14845551212', $data['number']);
        $this->assertSame('MyBrand', $data['brand']);
        $this->assertArrayHasKey('sender_id', $data);
        $this->assertArrayHasKey('code_length', $data);
        $this->assertArrayHasKey('pin_expiry', $data);
        $this->assertArrayHasKey('next_event_wait', $data);
        $this->assertArrayHasKey('workflow_id', $data);
    }

    public function testToArrayOmitsEmptyCountryAndLocale(): void
    {
        $data = (new StartVerification('14845551212', 'MyBrand'))->toArray();

        $this->assertArrayNotHasKey('country', $data);
        $this->assertArrayNotHasKey('lg', $data);
    }

    public function testToArrayIncludesCountryWhenSet(): void
    {
        $data = (new StartVerification('14845551212', 'MyBrand'))->setCountry('GB')->toArray();

        $this->assertSame('GB', $data['country']);
    }

    public function testToArrayIncludesLocaleWhenSet(): void
    {
        $data = (new StartVerification('14845551212', 'MyBrand'))->setLocale('en-gb')->toArray();

        $this->assertSame('en-gb', $data['lg']);
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    public function testInvalidCountryThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Country must be in two character format');

        (new StartVerification('14845551212', 'MyBrand'))->setCountry('GER');
    }

    public function testInvalidCodeLengthThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf('Pin length must be either %d or %d digits', StartVerification::PIN_LENGTH_4, StartVerification::PIN_LENGTH_6)
        );

        (new StartVerification('14845551212', 'MyBrand'))->setCodeLength(5);
    }

    public function testPinExpiryTooLowThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Pin expiration must be between 60 and 3600 seconds');

        (new StartVerification('14845551212', 'MyBrand'))->setPinExpiry(30);
    }

    public function testNextEventWaitTooLowThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Next Event time must be between 60 and 900 seconds');

        (new StartVerification('14845551212', 'MyBrand'))->setNextEventWait(30);
    }

    public function testWorkflowIdOutOfRangeThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Workflow ID must be from 1 to 7');

        (new StartVerification('14845551212', 'MyBrand'))->setWorkflowId(8);
    }

    public function testInvalidWorkflowIdInConstructorThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new StartVerification('14845551212', 'MyBrand', 99);
    }
}
