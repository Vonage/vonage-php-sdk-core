<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Account;

use Vonage\Account\Secret;
use Vonage\InvalidResponseException;
use PHPUnit\Framework\TestCase;

class SecretTest extends TestCase
{
    public function setUp(): void
    {
        $this->secret = @Secret::fromApi([
            'id' => 'ad6dc56f-07b5-46e1-a527-85530e625800',
            'created_at' => '2017-03-02T16:34:49Z',
            '_links' => [
                'self' => [
                    'href' => '/accounts/abcd1234/secrets/ad6dc56f-07b5-46e1-a527-85530e625800'
                ]
            ]
        ]);
    }

    public function testRejectsInvalidDataNoId()
    {
        $this->expectException(InvalidResponseException::class);
        @Secret::fromApi(['id' => 'abc']);
    }

    public function testRejectsInvalidDataNoCreatedAt()
    {
        $this->expectException(InvalidResponseException::class);
        @Secret::fromApi(['created_at' => '2017-03-02T16:34:49Z']);
    }

    public function testObjectAccess()
    {
        $this->assertEquals('ad6dc56f-07b5-46e1-a527-85530e625800', $this->secret->getId());
        $this->assertEquals('2017-03-02T16:34:49Z', $this->secret->getCreatedAt());
    }

    public function testArrayAccess()
    {
        $this->assertEquals('ad6dc56f-07b5-46e1-a527-85530e625800', @$this->secret['id']);
        $this->assertEquals('2017-03-02T16:34:49Z', @$this->secret['created_at']);
    }
}
