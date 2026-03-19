<?php

declare(strict_types=1);

namespace VonageTest\Client\Exception;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Exception\TransportException;

class TransportTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(TransportException::class);

        throw new TransportException('Response was empty');
    }

    public function testExceptionMessage(): void
    {
        $message = 'Response was empty';
        $exception = new TransportException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testExceptionIsInstanceOfBaseException(): void
    {
        $exception = new TransportException('Response was empty');

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
