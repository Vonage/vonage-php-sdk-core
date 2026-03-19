<?php

declare(strict_types=1);

namespace VonageTest\Client\Exception;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Exception\CredentialsException;

class CredentialsTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(CredentialsException::class);

        throw new CredentialsException('You are not authorised to perform this request');
    }

    public function testExceptionMessage(): void
    {
        $message = 'You are not authorised to perform this request';
        $exception = new CredentialsException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testExceptionIsInstanceOfBaseException(): void
    {
        $exception = new CredentialsException('You are not authorised to perform this request');

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
