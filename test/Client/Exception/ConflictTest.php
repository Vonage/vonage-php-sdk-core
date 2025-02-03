<?php

declare(strict_types=1);

namespace VonageTest\Client\Exception;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Exception\Conflict;

class ConflictTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(Conflict::class);

        throw new Conflict('Conflict occurred');
    }

    public function testExceptionMessage(): void
    {
        $message = 'Conflict occurred';
        $exception = new Conflict($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testExceptionIsInstanceOfBaseException(): void
    {
        $exception = new Conflict('Conflict occurred');

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
