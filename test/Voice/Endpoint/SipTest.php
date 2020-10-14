<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\Test\Voice\Endpoint;

use PHPUnit\Framework\TestCase;
use Vonage\Voice\Endpoint\SIP;

class SipTest extends TestCase
{
    /**
     * @var string
     */
    protected $uri = 'sip:rebekka@sip.example.com';

    /**
     * @var string
     */
    protected $type = 'sip';

    public function testDefaultEndpointIsCreatedProperly(): void
    {
        $endpoint = new SIP($this->uri);

        self::assertSame($this->uri, $endpoint->getId());
        self::assertEmpty($endpoint->getHeaders());
    }

    public function testFactoryCreatesAppEndpoint(): void
    {
        $headers = [
            'location' => 'New York City',
            'occupation' => 'Developer'
        ];

        $endpoint = SIP::factory($this->uri, $headers);

        self::assertSame($this->uri, $endpoint->getId());
        self::assertSame($headers, $endpoint->getHeaders());
    }

    public function testToArrayHasCorrectStructure(): void
    {
        self::assertSame([
            'type' => $this->type,
            'uri' => $this->uri
        ], (new SIP($this->uri))->toArray());
    }

    public function testHeadersAreReturnedAsArray(): void
    {
        $headers = [
            'location' => 'New York City',
            'occupation' => 'Developer'
        ];

        $expected = [
            'type' => $this->type,
            'uri' => $this->uri,
            'headers' => $headers
        ];

        self::assertSame($expected, ((new SIP($this->uri))->setHeaders($headers))->toArray());
    }

    public function testSerializesToJSONCorrectly(): void
    {
        self::assertSame([
            'type' => $this->type,
            'uri' => $this->uri
        ], (new SIP($this->uri))->jsonSerialize());
    }

    public function testHeaderCanBeIndividuallyAdded(): void
    {
        self::assertSame(['key' => 'value'], (new SIP($this->uri))->addHeader('key', 'value')->getHeaders());
    }
}
