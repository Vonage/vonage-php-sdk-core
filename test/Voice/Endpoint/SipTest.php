<?php
declare(strict_types=1);

namespace VonageTest\Voice\Endpoint;

use Vonage\Voice\Endpoint\SIP;
use PDO;
use PHPUnit\Framework\TestCase;

class SipTest extends TestCase
{
    public function testDefaultEndpointIsCreatedProperly()
    {
        $endpoint = new SIP('sip:rebekka@sip.example.com');
        $this->assertSame('sip:rebekka@sip.example.com', $endpoint->getId());
        $this->assertEmpty($endpoint->getHeaders());
    }

    public function testFactoryCreatesAppEndpoint()
    {
        $headers = [
            'location' => 'New York City',
            'occupation' => 'Developer'
        ];

        $endpoint = SIP::factory('sip:rebekka@sip.example.com', $headers);

        $this->assertSame('sip:rebekka@sip.example.com', $endpoint->getId());
        $this->assertSame($headers, $endpoint->getHeaders());
    }

    public function testToArrayHasCorrectStructure()
    {
        $expected = [
            'type' => 'sip',
            'uri' => 'sip:rebekka@sip.example.com',
        ];
        
        $endpoint = new SIP("sip:rebekka@sip.example.com");
        $this->assertSame($expected, $endpoint->toArray());
    }

    public function testHeadersAreReturnedAsArray()
    {
        $headers = [
            'location' => 'New York City',
            'occupation' => 'Developer'
        ];

        $expected = [
            'type' => 'sip',
            'uri' => 'sip:rebekka@sip.example.com',
            'headers' => $headers
        ];
        
        $endpoint = new SIP('sip:rebekka@sip.example.com');
        $endpoint->setHeaders($headers);
        $this->assertSame($expected, $endpoint->toArray());
    }

    public function testSerializesToJSONCorrectly()
    {
        $expected = [
            'type' => 'sip',
            'uri' => 'sip:rebekka@sip.example.com',
        ];
        
        $endpoint = new SIP('sip:rebekka@sip.example.com');
        $this->assertSame($expected, $endpoint->jsonSerialize());
    }

    public function testHeaderCanBeIndividuallyAdded()
    {
        $endpoint = new SIP('sip:rebekka@sip.example.com');
        $endpoint->addHeader('key', 'value');

        $this->assertSame(['key' => 'value'], $endpoint->getHeaders());
    }
}
