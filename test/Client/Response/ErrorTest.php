<?php

declare(strict_types=1);

namespace VonageTest\Client\Response;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Response\Error;

class ErrorTest extends TestCase
{
    public function testErrorInstance(): void
    {
        $data = [
            'status' => '400',
            'error_text' => 'Invalid request'
        ];

        $error = new Error($data);

        $this->assertInstanceOf(Error::class, $error);
        $this->assertTrue($error->isError());
        $this->assertFalse($error->isSuccess());
        $this->assertEquals(400, $error->getCode());
        $this->assertEquals('Invalid request', $error->getMessage());
    }

    public function testErrorTextNormalization(): void
    {
        $data = [
            'status' => '500',
            'error_text' => 'Internal Server Error'
        ];

        $error = new Error($data);

        $this->assertEquals(500, $error->getCode());
        $this->assertEquals('Internal Server Error', $error->getMessage());
    }
}
