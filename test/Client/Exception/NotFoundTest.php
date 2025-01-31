<?php

declare(strict_types=1);

namespace VonageTest\Client\Exception;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Exception\NotFound;

class NotFoundTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(NotFound::class);

        throw new NotFound('You are not authorised to perform this request');
    }

    public function testExceptionMessage(): void
    {
        $message = 'You are not authorised to perform this request';
        $exception = new NotFound($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testExceptionIsInstanceOfBaseException(): void
    {
        $exception = new NotFound('You are not authorised to perform this request');

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
