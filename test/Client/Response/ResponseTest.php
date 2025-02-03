<?php

declare(strict_types=1);

namespace VonageTest\Client\Response;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Response\Response;
use RuntimeException;

class ResponseTest extends TestCase
{
    public function testConstructorWithExpectedKeys()
    {
        $stub = new class(['key1' => 'value1', 'key2' => 'value2']) extends Response {
            protected $expected = ['key1', 'key2'];
        };

        $this->assertInstanceOf(Response::class, $stub);
    }

    public function testConstructorThrowsExceptionOnMissingKeys()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('missing expected response keys: key2');

        new class(['key1' => 'value1']) extends Response {
            protected $expected = ['key1', 'key2'];
        };
    }
}
