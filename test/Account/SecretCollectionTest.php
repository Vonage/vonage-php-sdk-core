<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Account;

use Nexmo\Account\Secret;
use Nexmo\Account\SecretCollection;
use PHPUnit\Framework\TestCase;

class SecretCollectionTest extends TestCase
{
    /**
     * @var array<string, array>
     */
    protected $links;

    /**
     * @var array<Secret>
     */
    protected $secrets;

    public function setUp()
    {
        $this->secrets = [
            new Secret(
                'ad6dc56f-07b5-46e1-a527-85530e625800',
                '2017-03-02T16:34:49Z',
                [
                    'self' => [
                        'href' => '/accounts/abcd1234/secrets/ad6dc56f-07b5-46e1-a527-85530e625800'
                    ]
                ]
            )
        ];

        $this->links = [
            'self' => [
                'href' => '/accounts/abcd1234/secrets'
            ]
        ];

        $this->collection = new SecretCollection($this->secrets, $this->links);
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
        $this->assertEquals($this->secrets, $this->collection->getSecrets());
    }
}
