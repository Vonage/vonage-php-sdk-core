<?php

declare(strict_types=1);

namespace VonageTest\Client\Exception;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Exception\Credentials;

class CredentialsTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(Credentials::class);

        throw new Credentials('You are not authorised to perform this request');
    }

    public function testExceptionMessage(): void
    {
        $message = 'You are not authorised to perform this request';
        $exception = new Credentials($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testExceptionIsInstanceOfBaseException(): void
    {
        $exception = new Credentials('You are not authorised to perform this request');

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
