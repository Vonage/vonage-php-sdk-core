<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Voice\Endpoint;

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
        $this->assertSame($this->uri, (new Websocket($this->uri))->getId());
    }

    public function testCanAddHeader(): void
    {
        $endpoint = (new Websocket($this->uri))->addHeader('key', 'value');

        $this->assertSame($this->uri, $endpoint->getId());
        $this->assertSame(['key' => 'value'], $endpoint->getHeaders());
    }

    public function testFactoryCreatesWebsocketEndpoint(): void
    {
        $this->assertSame($this->uri, (Websocket::factory($this->uri))->getId());
    }

    public function testFactoryCreatesAdditionalOptions(): void
    {
        $endpoint = Websocket::factory($this->uri, [
            'headers' => ['key' => 'value'],
            'content-type' => Websocket::TYPE_16000
        ]);

        $this->assertSame($this->uri, $endpoint->getId());
        $this->assertSame(['key' => 'value'], $endpoint->getHeaders());
        $this->assertSame(Websocket::TYPE_16000, $endpoint->getContentType());
    }

    public function testToArrayHasCorrectStructure(): void
    {
        $this->assertSame([
            'type' => 'websocket',
            'uri' => $this->uri,
            'content-type' => Websocket::TYPE_8000
        ], (new Websocket($this->uri))->toArray());
    }

    public function testToArrayAddsHeaders(): void
    {
        $headers = ['key' => 'value'];

        $this->assertSame([
            'type' => 'websocket',
            'uri' => $this->uri,
            'content-type' => Websocket::TYPE_8000,
            'headers' => $headers,
        ], (new Websocket($this->uri))->setHeaders($headers)->toArray());
    }

    public function testSerializesToJSONCorrectly(): void
    {
        $this->assertSame([
            'type' => 'websocket',
            'uri' => $this->uri,
            'content-type' => Websocket::TYPE_8000
        ], (new Websocket($this->uri))->jsonSerialize());
    }
}
