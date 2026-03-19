<?php

declare(strict_types=1);

namespace VonageTest\Client\Exception;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Exception\ConflictException;

class ConflictTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(ConflictException::class);

        throw new ConflictException('Conflict occurred');
    }

    public function testExceptionMessage(): void
    {
        $message = 'Conflict occurred';
        $exception = new ConflictException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testExceptionIsInstanceOfBaseException(): void
    {
        $exception = new ConflictException('Conflict occurred');

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
