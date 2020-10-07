<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test\Account;

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
        self::assertEquals('ad6dc56f-07b5-46e1-a527-85530e625800', $this->secret->getId());
        self::assertEquals('2017-03-02T16:34:49Z', $this->secret->getCreatedAt());
    }

    public function testArrayAccess(): void
    {
        self::assertEquals('ad6dc56f-07b5-46e1-a527-85530e625800', @$this->secret['id']);
        self::assertEquals('2017-03-02T16:34:49Z', @$this->secret['created_at']);
    }
}
