<?php

declare(strict_types=1);

namespace VonageTest\Client\Exception;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Exception\Request;

class RequestTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(Request::class);

        throw new Request('You are not authorised to perform this request');
    }

    public function testExceptionMessage(): void
    {
        $message = 'You are not authorised to perform this request';
        $exception = new Request($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testExceptionIsInstanceOfBaseException(): void
    {
        $exception = new Request('You are not authorised to perform this request');

        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testSetAndGetRequestId(): void
    {
        $exception = new Request('Test exception');
        $requestId = '12345';

        $exception->setRequestId($requestId);

        $this->assertSame($requestId, $exception->getRequestId());
    }

    public function testSetAndGetNetworkId(): void
    {
        $exception = new Request('Test exception');
        $networkId = '67890';

        $exception->setNetworkId($networkId);

        $this->assertSame($networkId, $exception->getNetworkId());
    }
}
