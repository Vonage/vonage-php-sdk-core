<?php
declare(strict_types=1);

namespace VonageTest\Voice\Endpoint;

use Vonage\Voice\Endpoint\App;
use Vonage\Voice\Endpoint\SIP;
use Vonage\Voice\Endpoint\VBC;
use Vonage\Voice\Endpoint\Phone;
use PHPUnit\Framework\TestCase;
use Vonage\Voice\Endpoint\Websocket;
use Vonage\Voice\Endpoint\EndpointFactory;

class EndpointFactoryTest extends TestCase
{
    public function testCanCreateAppEndpoint()
    {
        $factory = new EndpointFactory();
        $endpoint = $factory->create([
            'type' => 'app',
            'user' => 'username',
        ]);

        $this->assertTrue($endpoint instanceof App);
    }

    public function testCanCreatePhoneEndpoint()
    {
        $data = [
            'type' => 'phone',
            'number' => '15551231234',
            'onAnswer' => [
                'url' => 'https://test.domain/answerNCCO.json',
                'ringbackTone' => 'https://test.domain/ringback.mp3'
            ]
        ];

        $factory = new EndpointFactory();
        $endpoint = $factory->create($data);

        $this->assertTrue($endpoint instanceof Phone);
        $this->assertSame($data, $endpoint->toArray());
    }

    public function testCanCreateSIPEndpoint()
    {
        $data = [
            'type' => 'sip',
            'uri' => 'sip:rebekka@sip.example.com',
        ];

        $factory = new EndpointFactory();
        $endpoint = $factory->create($data);

        $this->assertTrue($endpoint instanceof SIP);
        $this->assertSame($data['uri'], $endpoint->getId());
    }

    public function testCanCreateVBCEndpoint()
    {
        $data = [
            'type' => 'vbc',
            'extension' => '123',
        ];

        $factory = new EndpointFactory();
        $endpoint = $factory->create($data);

        $this->assertTrue($endpoint instanceof VBC);
        $this->assertSame($data, $endpoint->toArray());
    }

    public function testCanCreateWebsocketEndpoint()
    {
        $data = [
            'type' => 'websocket',
            'uri' => 'https://testdomain.com/websocket',
            'content-type' => 'audio/116;rate=8000',
            'headers' => ['key' => 'value'],
        ];

        $factory = new EndpointFactory();
        $endpoint = $factory->create($data);

        $this->assertTrue($endpoint instanceof Websocket);
        $this->assertSame($data, $endpoint->toArray());
    }

    public function testThrowsExceptionOnUnknownEndpoint()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown endpoint type');

        $factory = new EndpointFactory();
        $factory->create([
            'type' => 'foo',
            'user' => 'username',
        ]);
    }
}
