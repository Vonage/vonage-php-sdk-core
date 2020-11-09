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
use RuntimeException;
use Vonage\Voice\Endpoint\App;
use Vonage\Voice\Endpoint\EndpointFactory;
use Vonage\Voice\Endpoint\Phone;
use Vonage\Voice\Endpoint\SIP;
use Vonage\Voice\Endpoint\VBC;
use Vonage\Voice\Endpoint\Websocket;

class EndpointFactoryTest extends TestCase
{
    public function testCanCreateAppEndpoint(): void
    {
        $this->assertInstanceOf(App::class, (new EndpointFactory())->create([
            'type' => 'app',
            'user' => 'username',
        ]));
    }

    public function testCanCreatePhoneEndpoint(): void
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

        $this->assertInstanceOf(Phone::class, $endpoint);
        $this->assertSame($data, $endpoint->toArray());
    }

    public function testCanCreateSIPEndpoint(): void
    {
        $data = [
            'type' => 'sip',
            'uri' => 'sip:rebekka@sip.example.com',
        ];
        $endpoint = (new EndpointFactory())->create($data);

        $this->assertInstanceOf(SIP::class, $endpoint);
        $this->assertSame($data['uri'], $endpoint->getId());
    }

    public function testCanCreateVBCEndpoint(): void
    {
        $data = [
            'type' => 'vbc',
            'extension' => '123',
        ];
        $endpoint = (new EndpointFactory())->create($data);

        $this->assertInstanceOf(VBC::class, $endpoint);
        $this->assertSame($data, $endpoint->toArray());
    }

    public function testCanCreateWebsocketEndpoint(): void
    {
        $data = [
            'type' => 'websocket',
            'uri' => 'https://testdomain.com/websocket',
            'content-type' => 'audio/116;rate=8000',
            'headers' => ['key' => 'value'],
        ];
        $endpoint = (new EndpointFactory())->create($data);

        $this->assertInstanceOf(Websocket::class, $endpoint);
        $this->assertSame($data, $endpoint->toArray());
    }

    public function testThrowsExceptionOnUnknownEndpoint(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown endpoint type');

        (new EndpointFactory())
            ->create([
                'type' => 'foo',
                'user' => 'username',
            ]);
    }
}
