<?php

declare(strict_types=1);

namespace VonageTest\Verify2\Request;

use PHPUnit\Framework\TestCase;
use Vonage\Verify2\Request\BaseVerifyRequest;
use Vonage\Verify2\VerifyObjects\VerificationLocale;
use Vonage\Verify2\VerifyObjects\VerificationWorkflow;

class BaseVerifyRequestTest extends TestCase
{
    public function testGetSetLocale(): void
    {
        $request = $this->getMockForAbstractClass(BaseVerifyRequest::class);
        $locale = new VerificationLocale('en-us');

        $this->assertNull($request->getLocale());
        $request->setLocale($locale);
        $this->assertSame($locale, $request->getLocale());
    }

    public function testGetSetTimeout(): void
    {
        $request = $this->getMockForAbstractClass(BaseVerifyRequest::class);

        $this->assertEquals(300, $request->getTimeout());
        $request->setTimeout(120);
        $this->assertEquals(120, $request->getTimeout());

        $this->expectException(\OutOfBoundsException::class);
        $request->setTimeout(50);
    }

    public function testGetSetCode(): void
    {
        $request = $this->getMockForAbstractClass(BaseVerifyRequest::class);

        $this->assertNull($request->getCode());
        $request->setCode('1234');
        $this->assertSame('1234', $request->getCode());
    }

    public function testGetSetClientRef(): void
    {
        $request = $this->getMockForAbstractClass(BaseVerifyRequest::class);

        $this->assertNull($request->getClientRef());
        $request->setClientRef('ref123');
        $this->assertSame('ref123', $request->getClientRef());
    }

    public function testGetSetLength(): void
    {
        $request = $this->getMockForAbstractClass(BaseVerifyRequest::class);

        $this->assertEquals(4, $request->getLength());
        $request->setLength(6);
        $this->assertEquals(6, $request->getLength());
        $this->expectException(\OutOfBoundsException::class);
        $request->setLength(2);
    }

    public function testGetSetBrand(): void
    {
        $request = $this->getMockForAbstractClass(BaseVerifyRequest::class);

        $request->setBrand('MyBrand');
        $this->assertSame('MyBrand', $request->getBrand());
    }

    public function testAddGetWorkflows(): void
    {
        $request = $this->getMockForAbstractClass(BaseVerifyRequest::class);
        $workflow = new VerificationWorkflow('sms', '07778987987');

        $this->assertCount(0, $request->getWorkflows());
        $request->addWorkflow($workflow);
        $this->assertCount(1, $request->getWorkflows());
        $this->assertSame([$workflow->toArray()], $request->getWorkflows());
    }

    public function testGetSetFraudCheck(): void
    {
        $request = $this->getMockForAbstractClass(BaseVerifyRequest::class);

        $this->assertNull($request->getFraudCheck());
        $request->setFraudCheck(true);
        $this->assertTrue($request->getFraudCheck());

        $request->setFraudCheck(false);
        $this->assertFalse($request->getFraudCheck());
    }

    public function testGetBaseVerifyUniversalOutputArray(): void
    {
        $request = $this->getMockForAbstractClass(BaseVerifyRequest::class);
        $request->setLocale(new VerificationLocale('en-us'));
        $request->setTimeout(120);
        $request->setCode('1234');
        $request->setClientRef('ref123');
        $request->setLength(6);
        $request->setBrand('MyBrand');
        $workflow = new VerificationWorkflow('sms', '0778798149684');
        $request->addWorkflow($workflow);
        $request->setFraudCheck(true);

        $expectedArray = [
            'locale' => 'en-us',
            'channel_timeout' => 120,
            'code_length' => 6,
            'brand' => 'MyBrand',
            'workflow' => [$workflow->toArray()],
            'client_ref' => 'ref123',
            'code' => '1234',
        ];

        $this->assertSame($expectedArray, $request->getBaseVerifyUniversalOutputArray());
    }
}
