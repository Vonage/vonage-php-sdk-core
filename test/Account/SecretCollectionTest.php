<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Account;

use Vonage\Account\Secret;
use Vonage\Account\SecretCollection;
use PHPUnit\Framework\TestCase;

class SecretCollectionTest extends TestCase
{
    public function setUp(): void
    {
        $this->secrets = [[
            'id' => 'ad6dc56f-07b5-46e1-a527-85530e625800',
            'created_at' => '2017-03-02T16:34:49Z',
            '_links' => [
                'self' => [
                    'href' => '/accounts/abcd1234/secrets/ad6dc56f-07b5-46e1-a527-85530e625800'
                ]
            ]
        ]];

        $this->links = [
            'self' => [
                'href' => '/accounts/abcd1234/secrets'
            ]
        ];

        $this->collection = @SecretCollection::fromApi([
            '_links' => $this->links,
            '_embedded' => [
                'secrets' => $this->secrets
            ]
        ]);
    }

    public function testGetSecrets()
    {
        $secrets = $this->collection->getSecrets();
        $this->assertInstanceOf(Secret::class, $secrets[0]);
    }

    public function testGetLinks()
    {
        $this->assertArrayHasKey('self', $this->collection->getLinks());
    }

    public function testObjectAccess()
    {
        $this->assertEquals($this->links, $this->collection->getLinks());

        $secrets = array_map(function ($v) {
            return @Secret::fromApi($v);
        }, $this->secrets);
        $this->assertEquals($secrets, $this->collection->getSecrets());
    }

    public function testArrayAccess()
    {
        $this->assertEquals($this->links, @$this->collection['_links']);

        $secrets = array_map(function ($v) {
            return @Secret::fromApi($v);
        }, $this->secrets);
        $this->assertEquals($secrets, @$this->collection['secrets']);
    }
}
