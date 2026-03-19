<?php

declare(strict_types=1);

namespace VonageTest\Client\Exception;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Exception\RequestException;

class RequestTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(RequestException::class);

        throw new RequestException('You are not authorised to perform this request');
    }

    public function testExceptionMessage(): void
    {
        $message = 'You are not authorised to perform this request';
        $exception = new RequestException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testExceptionIsInstanceOfBaseException(): void
    {
        $exception = new RequestException('You are not authorised to perform this request');

        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testSetAndGetRequestId(): void
    {
        $exception = new RequestException('Test exception');
        $requestId = '12345';

        $exception->setRequestId($requestId);

        $this->assertSame($requestId, $exception->getRequestId());
    }

    public function testSetAndGetNetworkId(): void
    {
        $exception = new RequestException('Test exception');
        $networkId = '67890';

        $exception->setNetworkId($networkId);

        $this->assertSame($networkId, $exception->getNetworkId());
    }
}
