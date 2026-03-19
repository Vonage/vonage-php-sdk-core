<?php

declare(strict_types=1);

namespace VonageTest\Client\Exception;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Exception\NotFoundException;

class NotFoundTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(NotFoundException::class);

        throw new NotFoundException('You are not authorised to perform this request');
    }

    public function testExceptionMessage(): void
    {
        $message = 'You are not authorised to perform this request';
        $exception = new NotFoundException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testExceptionIsInstanceOfBaseException(): void
    {
        $exception = new NotFoundException('You are not authorised to perform this request');

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
