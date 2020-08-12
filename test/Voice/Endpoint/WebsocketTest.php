<?php
declare(strict_types=1);

namespace VonageTest\Voice\Endpoint;

use Vonage\Voice\Endpoint\Websocket;
use PHPUnit\Framework\TestCase;

class WebsocketTest extends TestCase
{
    public function testSetsURLAtCreation()
    {
        $endpoint = new Websocket("https://testdomain.com/websocket");
        $this->assertSame("https://testdomain.com/websocket", $endpoint->getId());
    }

    public function testCanAddHeader()
    {
        $endpoint = new Websocket("https://testdomain.com/websocket");
        $endpoint->addHeader('key', 'value');
        $this->assertSame("https://testdomain.com/websocket", $endpoint->getId());
        $this->assertSame(['key' => 'value'], $endpoint->getHeaders());
    }

    public function testFactoryCreatesWebsocketEndpoint()
    {
        $endpoint = Websocket::factory('https://testdomain.com/websocket');
        $this->assertSame("https://testdomain.com/websocket", $endpoint->getId());
    }

    public function testFactoryCreatesAdditionalOptions()
    {
        $endpoint = Websocket::factory('https://testdomain.com/websocket', [
            'headers' => ['key' => 'value'],
            'content-type' => Websocket::TYPE_16000
        ]);
        $this->assertSame("https://testdomain.com/websocket", $endpoint->getId());
        $this->assertSame(['key' => 'value'], $endpoint->getHeaders());
        $this->assertSame(Websocket::TYPE_16000, $endpoint->getContentType());
    }

    public function testToArrayHasCorrectStructure()
    {
        $expected = [
            'type' => 'websocket',
            'uri' => 'https://testdomain.com/websocket',
            'content-type' => 'audio/116;rate=8000'
        ];
        
        $endpoint = new Websocket("https://testdomain.com/websocket");
        $this->assertSame($expected, $endpoint->toArray());
    }

    public function testToArrayAddsHeaders()
    {
        $expected = [
            'type' => 'websocket',
            'uri' => 'https://testdomain.com/websocket',
            'content-type' => 'audio/116;rate=8000',
            'headers' => ['key' => 'value'],
        ];
        
        $endpoint = new Websocket("https://testdomain.com/websocket");
        $endpoint->setHeaders($expected['headers']);
        
        $this->assertSame($expected, $endpoint->toArray());
    }

    public function testSerializesToJSONCorrectly()
    {
        $expected = [
            'type' => 'websocket',
            'uri' => 'https://testdomain.com/websocket',
            'content-type' => 'audio/116;rate=8000'
        ];
        
        $endpoint = new Websocket("https://testdomain.com/websocket");
        $this->assertSame($expected, $endpoint->jsonSerialize());
    }
}
