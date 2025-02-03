<?php

declare(strict_types=1);

namespace VonageTest\Client\Exception;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Exception\Request;

class TransportTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(Request::class);

        throw new Request('Response was empty');
    }

    public function testExceptionMessage(): void
    {
        $message = 'Response was empty';
        $exception = new Request($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testExceptionIsInstanceOfBaseException(): void
    {
        $exception = new Request('Response was empty');

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
