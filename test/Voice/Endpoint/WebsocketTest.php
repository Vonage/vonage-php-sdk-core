<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test\Voice\Endpoint;

use PHPUnit\Framework\TestCase;
use Vonage\Voice\Endpoint\Websocket;

class WebsocketTest extends TestCase
{
    /**
     * @var string
     */
    protected $uri = 'https://testdomain.com/websocket';

    public function testSetsURLAtCreation(): void
    {
        self::assertSame($this->uri, (new Websocket($this->uri))->getId());
    }

    public function testCanAddHeader(): void
    {
        $endpoint = (new Websocket($this->uri))->addHeader('key', 'value');

        self::assertSame($this->uri, $endpoint->getId());
        self::assertSame(['key' => 'value'], $endpoint->getHeaders());
    }

    public function testFactoryCreatesWebsocketEndpoint(): void
    {
        self::assertSame($this->uri, (Websocket::factory($this->uri))->getId());
    }

    public function testFactoryCreatesAdditionalOptions(): void
    {
        $endpoint = Websocket::factory($this->uri, [
            'headers' => ['key' => 'value'],
            'content-type' => Websocket::TYPE_16000
        ]);

        self::assertSame($this->uri, $endpoint->getId());
        self::assertSame(['key' => 'value'], $endpoint->getHeaders());
        self::assertSame(Websocket::TYPE_16000, $endpoint->getContentType());
    }

    public function testToArrayHasCorrectStructure(): void
    {
        self::assertSame([
            'type' => 'websocket',
            'uri' => $this->uri,
            'content-type' => Websocket::TYPE_8000
        ], (new Websocket($this->uri))->toArray());
    }

    public function testToArrayAddsHeaders(): void
    {
        $headers = ['key' => 'value'];

        self::assertSame([
            'type' => 'websocket',
            'uri' => $this->uri,
            'content-type' => Websocket::TYPE_8000,
            'headers' => $headers,
        ], (new Websocket($this->uri))->setHeaders($headers)->toArray());
    }

    public function testSerializesToJSONCorrectly(): void
    {
        self::assertSame([
            'type' => 'websocket',
            'uri' => $this->uri,
            'content-type' => Websocket::TYPE_8000
        ], (new Websocket($this->uri))->jsonSerialize());
    }
}
