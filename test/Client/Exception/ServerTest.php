<?php

declare(strict_types=1);

namespace VonageTest\Client\Exception;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Exception\ServerException;

class ServerTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(ServerException::class);

        throw new ServerException('No results found');
    }

    public function testExceptionMessage(): void
    {
        $message = 'No results found';
        $exception = new ServerException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testExceptionIsInstanceOfBaseException(): void
    {
        $exception = new ServerException('No results found');

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
