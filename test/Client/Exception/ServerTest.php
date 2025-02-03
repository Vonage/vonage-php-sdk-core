<?php

declare(strict_types=1);

namespace VonageTest\Client\Exception;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Exception\Request;

class ServerTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(Request::class);

        throw new Request('No results found');
    }

    public function testExceptionMessage(): void
    {
        $message = 'No results found';
        $exception = new Request($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testExceptionIsInstanceOfBaseException(): void
    {
        $exception = new Request('No results found');

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
