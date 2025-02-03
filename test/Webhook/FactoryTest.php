<?php

declare(strict_types=1);

namespace VonageTest\Webhook;

use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Vonage\Webhook\Factory;
use Laminas\Diactoros\Stream;

class FactoryTest extends TestCase
{
    /**
     * A concrete implementation of the Factory class for testing.
     */
    private $concreteFactory;

    protected function setUp(): void
    {
        // Create a simple concrete implementation of the abstract Factory class for testing.
        $this->concreteFactory = new class extends Factory {
            public static function createFromArray(array $data)
            {
                return $data;
            }
        };
    }

    public function testCreateFromJsonWithValidJson(): void
    {
        $json = '{"key":"value"}';
        $result = $this->concreteFactory::createFromJson($json);

        $this->assertSame(['key' => 'value'], $result);
    }

    public function testCreateFromJsonWithInvalidJson(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Invalid JSON string detected for webhook transformation");

        $json = '{invalid_json}';
        $this->concreteFactory::createFromJson($json);
    }

    public function testCreateFromRequestWithGetMethod(): void
    {
        $request = new ServerRequest([], [], null, 'GET', 'php://temp', [], [], ['key' => 'value']);

        $result = $this->concreteFactory::createFromRequest($request);

        $this->assertSame(['key' => 'value'], $result);
    }

    public function testCreateFromRequestWithPostMethodAndJson(): void
    {
        $body = '{"key":"value"}';

        // Use a writable temporary stream for the body
        $stream = new Stream('php://temp', 'wb+');
        $stream->write($body);
        $stream->rewind();

        $request = new ServerRequest([], [], null, 'POST', $stream, ['Content-Type' => 'application/json'], [], []);

        $result = $this->concreteFactory::createFromRequest($request);

        $this->assertSame(['key' => 'value'], $result);
    }

    public function testCreateFromRequestWithInvalidMethod(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Invalid method for incoming webhook");

        $request = new ServerRequest([], [], null, 'PUT');
        $this->concreteFactory::createFromRequest($request);
    }
}
