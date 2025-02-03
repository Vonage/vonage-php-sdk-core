<?php

declare(strict_types=1);

namespace VonageTest\Client\Response;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Response\AbstractResponse;

class AbstractResponseTest extends TestCase
{
    public function testGetDataReturnsCorrectData()
    {
        $mock = $this->getMockForAbstractClass(AbstractResponse::class);

        $reflection = new \ReflectionClass($mock);
        $property = $reflection->getProperty('data');
        $property->setAccessible(true);

        $testData = ['key' => 'value'];
        $property->setValue($mock, $testData);

        $this->assertSame($testData, $mock->getData());
    }

    public function testIsSuccessReturnsTrueWhenStatusIsZero()
    {
        $mock = $this->getMockForAbstractClass(AbstractResponse::class);

        $reflection = new \ReflectionClass($mock);
        $property = $reflection->getProperty('data');
        $property->setAccessible(true);

        $property->setValue($mock, ['status' => 0]);

        $this->assertTrue($mock->isSuccess());
    }

    public function testIsSuccessReturnsFalseWhenStatusIsNotZero()
    {
        $mock = $this->getMockForAbstractClass(AbstractResponse::class);

        $reflection = new \ReflectionClass($mock);
        $property = $reflection->getProperty('data');
        $property->setAccessible(true);

        $property->setValue($mock, ['status' => 1]);

        $this->assertFalse($mock->isSuccess());
    }

    public function testIsErrorReturnsTrueWhenStatusIsNotZero()
    {
        $mock = $this->getMockForAbstractClass(AbstractResponse::class);

        $reflection = new \ReflectionClass($mock);
        $property = $reflection->getProperty('data');
        $property->setAccessible(true);

        $property->setValue($mock, ['status' => 1]);

        $this->assertTrue($mock->isError());
    }

    public function testIsErrorReturnsFalseWhenStatusIsZero()
    {
        $mock = $this->getMockForAbstractClass(AbstractResponse::class);

        $reflection = new \ReflectionClass($mock);
        $property = $reflection->getProperty('data');
        $property->setAccessible(true);

        $property->setValue($mock, ['status' => 0]);

        $this->assertFalse($mock->isError());
    }
}
