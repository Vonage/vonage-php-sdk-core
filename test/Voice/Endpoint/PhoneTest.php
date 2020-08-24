<?php
declare(strict_types=1);

namespace VonageTest\Voice\Endpoint;

use Vonage\Voice\Endpoint\Phone;
use PHPUnit\Framework\TestCase;

class PhoneTest extends TestCase
{
    public function testDefaultEndpointIsCreatedProperly()
    {
        $endpoint = new Phone('15551231234');
        $this->assertSame("15551231234", $endpoint->getId());
        $this->assertNull($endpoint->getDtmfAnswer());
        $this->assertNull($endpoint->getRingbackTone());
        $this->assertNull($endpoint->getUrl());
    }

    public function testFactoryCreatesPhoneEndpoint()
    {
        $endpoint = Phone::factory('15551231234', [
            'dtmfAnswer' => '12',
            'onAnswer' => [
                'url' => 'https://test.domain/answerNCCO.json',
                'ringbackTone' => 'https://test.domain/ringback.mp3'
            ]
        ]);

        $this->assertSame('15551231234', $endpoint->getId());
        $this->assertSame('https://test.domain/answerNCCO.json', $endpoint->getUrl());
        $this->assertSame('https://test.domain/ringback.mp3', $endpoint->getRingbackTone());
    }

    public function testFactoryHandlesLegacyRingbackArgument()
    {
        $endpoint = Phone::factory('15551231234', [
            'dtmfAnswer' => '12',
            'onAnswer' => [
                'url' => 'https://test.domain/answerNCCO.json',
                'ringback' => 'https://test.domain/ringback.mp3'
            ]
        ]);

        $this->assertSame('15551231234', $endpoint->getId());
        $this->assertSame('https://test.domain/answerNCCO.json', $endpoint->getUrl());
        $this->assertSame('https://test.domain/ringback.mp3', $endpoint->getRingbackTone());
    }

    public function testToArrayHasCorrectStructure()
    {
        $expected = [
            'type' => 'phone',
            'number' => '15551231234',
        ];
        
        $endpoint = new Phone("15551231234");
        $this->assertSame($expected, $endpoint->toArray());
    }

    public function testRingbackNotReturnedIfURLNotSet()
    {
        $expected = [
            'type' => 'phone',
            'number' => '15551231234',
        ];
        
        $endpoint = new Phone("15551231234");
        $endpoint->setRingbackTone('https://test.domain/ringback.mp3');
        $this->assertSame($expected, $endpoint->toArray());
    }

    public function testRingbackIsReturnedIfURLIsSet()
    {
        $expected = [
            'type' => 'phone',
            'number' => '15551231234',
            'onAnswer' => [
                'url' => 'https://test.domain/answerNCCO.json',
                'ringbackTone' => 'https://test.domain/ringback.mp3'
            ]
        ];
        
        $endpoint = new Phone("15551231234");
        $endpoint->setRingbackTone('https://test.domain/ringback.mp3');
        $endpoint->setUrl('https://test.domain/answerNCCO.json');
        $this->assertSame($expected, $endpoint->toArray());
    }

    public function testSerializesToJSONCorrectly()
    {
        $expected = [
            'type' => 'phone',
            'number' => '15551231234',
            'dtmfAnswer' => '123'
        ];
        
        $endpoint = new Phone("15551231234");
        $endpoint->setDtmfAnswer('123');
        $this->assertSame($expected, $endpoint->jsonSerialize());
    }
}
