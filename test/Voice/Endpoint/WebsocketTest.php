<?php

declare(strict_types=1);

namespace VonageTest\Voice\Endpoint;

use VonageTest\VonageTestCase;
use Vonage\Voice\Endpoint\Websocket;

class WebsocketTest extends VonageTestCase
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

    public function testContentTypeConstantsAreCorrect(): void
    {
        $this->assertSame('audio/l16;rate=8000', Websocket::TYPE_8000);
        $this->assertSame('audio/l16;rate=16000', Websocket::TYPE_16000);
        $this->assertSame('audio/l16;rate=24000', Websocket::TYPE_24000);
    }

    public function testCanSetAuthorizationVonage(): void
    {
        $authorization = ['type' => 'vonage'];
        $endpoint = (new Websocket($this->uri))->setAuthorization($authorization);

        $this->assertSame($authorization, $endpoint->getAuthorization());
    }

    public function testCanSetAuthorizationCustom(): void
    {
        $authorization = ['type' => 'custom', 'value' => 'Bearer abc123'];
        $endpoint = (new Websocket($this->uri))->setAuthorization($authorization);

        $this->assertSame($authorization, $endpoint->getAuthorization());
    }

    public function testFactoryCreatesAuthorizationFromData(): void
    {
        $authorization = ['type' => 'vonage'];
        $endpoint = Websocket::factory($this->uri, ['authorization' => $authorization]);

        $this->assertSame($authorization, $endpoint->getAuthorization());
    }

    public function testToArrayIncludesAuthorization(): void
    {
        $authorization = ['type' => 'custom', 'value' => 'Bearer abc123'];
        $endpoint = (new Websocket($this->uri))->setAuthorization($authorization);

        $result = $endpoint->toArray();

        $this->assertArrayHasKey('authorization', $result);
        $this->assertSame($authorization, $result['authorization']);
    }

    public function testToArrayExcludesAuthorizationWhenNotSet(): void
    {
        $result = (new Websocket($this->uri))->toArray();

        $this->assertArrayNotHasKey('authorization', $result);
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
