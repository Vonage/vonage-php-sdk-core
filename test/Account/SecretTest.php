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
use Vonage\InvalidResponseException;

class SecretTest extends TestCase
{
    /**
     * @var Secret
     */
    private $secret;

    /**
     * @throws InvalidResponseException
     */
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

    public function testRejectsInvalidDataNoId(): void
    {
        $this->expectException(InvalidResponseException::class);

        new Secret(['id' => 'abc']);
    }

    public function testRejectsInvalidDataNoCreatedAt(): void
    {
        $this->expectException(InvalidResponseException::class);

        new Secret(['created_at' => '2017-03-02T16:34:49Z']);
    }

    public function testObjectAccess(): void
    {
        $this->assertEquals('ad6dc56f-07b5-46e1-a527-85530e625800', $this->secret->getId());
        $this->assertEquals('2017-03-02T16:34:49Z', $this->secret->getCreatedAt());
    }

    public function testArrayAccess(): void
    {
        $this->assertEquals('ad6dc56f-07b5-46e1-a527-85530e625800', @$this->secret['id']);
        $this->assertEquals('2017-03-02T16:34:49Z', @$this->secret['created_at']);
    }
}
