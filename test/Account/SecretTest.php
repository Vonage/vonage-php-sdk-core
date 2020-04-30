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
        $this->secret = new Secret(
            'ad6dc56f-07b5-46e1-a527-85530e625800',
            '2017-03-02T16:34:49Z',
            [
                'self' => [
                    'href' => '/accounts/abcd1234/secrets/ad6dc56f-07b5-46e1-a527-85530e625800'
                ]
            ]
        );
    }

    public function testObjectAccess()
    {
        $this->assertEquals('ad6dc56f-07b5-46e1-a527-85530e625800', $this->secret->getId());
        $this->assertEquals('2017-03-02T16:34:49Z', $this->secret->getCreatedAt());
    }
}
