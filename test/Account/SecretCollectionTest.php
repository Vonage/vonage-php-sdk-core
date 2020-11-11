<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Account;

use PHPUnit\Framework\TestCase;
use Vonage\Account\Secret;
use Vonage\Account\SecretCollection;
use Vonage\InvalidResponseException;

use function array_map;

class SecretCollectionTest extends TestCase
{
    /**
     * @var array[]
     */
    private $secrets;

    /**
     * @var string[]
     */
    private $links;

    private $collection;

    /**
     * @throws InvalidResponseException
     */
    public function setUp(): void
    {
        $this->secrets = [
            [
                'id' => 'ad6dc56f-07b5-46e1-a527-85530e625800',
                'created_at' => '2017-03-02T16:34:49Z',
                '_links' => [
                    'self' => [
                        'href' => '/accounts/abcd1234/secrets/ad6dc56f-07b5-46e1-a527-85530e625800'
                    ]
                ]
            ]
        ];

        $this->links = [
            'self' => [
                'href' => '/accounts/abcd1234/secrets'
            ]
        ];

        $this->collection = new SecretCollection($this->secrets, $this->links);
    }

    public function testGetSecrets(): void
    {
        $secrets = $this->collection->getSecrets();

        $this->assertInstanceOf(Secret::class, $secrets[0]);
    }

    public function testGetLinks(): void
    {
        $this->assertArrayHasKey('self', $this->collection->getLinks());
    }

    /**
     * @throws InvalidResponseException
     */
    public function testObjectAccess(): void
    {
        $this->assertEquals($this->links, $this->collection->getLinks());

        $secrets = array_map(static function ($v) {
            return @Secret::fromApi($v);
        }, $this->secrets);

        $this->assertEquals($secrets, $this->collection->getSecrets());
    }

    /**
     * @throws InvalidResponseException
     */
    public function testArrayAccess(): void
    {
        $this->assertEquals($this->links, @$this->collection['_links']);

        $secrets = array_map(static function ($v) {
            return @Secret::fromApi($v);
        }, $this->secrets);

        $this->assertEquals($secrets, @$this->collection['secrets']);
    }
}
