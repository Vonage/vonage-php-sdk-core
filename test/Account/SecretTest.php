<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Account;

use Nexmo\Account\Secret;
use Nexmo\InvalidResponseException;
use PHPUnit\Framework\TestCase;

class SecretTest extends TestCase
{
    public function setUp()
    {
        $this->secret = new Secret([
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
        new Secret(['id' => 'abc']);
    }

    public function testRejectsInvalidDataNoCreatedAt()
    {
        $this->expectException(InvalidResponseException::class);
        new Secret(['created_at' => '2017-03-02T16:34:49Z']);
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
