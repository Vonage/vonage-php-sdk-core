<?php

declare(strict_types=1);

namespace Tests\Vonage\Client\Callback;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Callback\Callback;
use RuntimeException;
use InvalidArgumentException;

class CallbackTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mocking the $_GET and $_POST superglobals for testing purposes
        $_GET = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $_POST = [
            'key3' => 'value3',
            'key4' => 'value4',
        ];
    }

    public function testConstructorWithMissingKeys(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('missing expected callback keys: key1, key2');

        $callback = new class (['key3' => 'value3']) extends Callback {
            protected array $expected = ['key1', 'key2'];
        };
    }

    public function testConstructorWithAllKeys(): void
    {
        $callback = new class ([
            'key1' => 'value1',
            'key2' => 'value2',
        ]) extends Callback {
            protected array $expected = ['key1', 'key2'];
        };

        $this->assertSame([
            'key1' => 'value1',
            'key2' => 'value2',
        ], $callback->getData());
    }

    public function testFromEnvPost(): void
    {
        $callback = Callback::fromEnv(Callback::ENV_POST);

        $this->assertInstanceOf(Callback::class, $callback);
        $this->assertSame([
            'key3' => 'value3',
            'key4' => 'value4',
        ], $callback->getData());
    }

    public function testFromEnvGet(): void
    {
        $callback = Callback::fromEnv(Callback::ENV_GET);

        $this->assertInstanceOf(Callback::class, $callback);
        $this->assertSame([
            'key1' => 'value1',
            'key2' => 'value2',
        ], $callback->getData());
    }

    public function testFromEnvAll(): void
    {
        $callback = Callback::fromEnv(Callback::ENV_ALL);

        $this->assertInstanceOf(Callback::class, $callback);
        $this->assertSame([
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
            'key4' => 'value4',
        ], $callback->getData());
    }

    public function testFromEnvInvalidSource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid source: invalid');

        Callback::fromEnv('invalid');
    }
}
