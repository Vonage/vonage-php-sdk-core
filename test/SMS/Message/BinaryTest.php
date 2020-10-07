<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test\SMS\Message;

use PHPUnit\Framework\TestCase;
use Vonage\SMS\Message\Binary;

class BinaryTest extends TestCase
{
    public function testCanCreateBinaryMessage(): void
    {
        $data = (new Binary(
            '447700900000',
            '16105551212',
            'EA0601AE02056A0045C60C037761702E6F7A656B692E6875000801034F7A656B69000101',
            '0605040B8423F0'
        ))->toArray();

        self::assertSame('447700900000', $data['to']);
        self::assertSame('16105551212', $data['from']);
        self::assertSame('EA0601AE02056A0045C60C037761702E6F7A656B692E6875000801034F7A656B69000101', $data['body']);
        self::assertSame('0605040B8423F0', $data['udh']);
    }

    public function testCanCreateBinaryMessageWithProtocolID(): void
    {
        $data = (new Binary(
            '447700900000',
            '16105551212',
            'EA0601AE02056A0045C60C037761702E6F7A656B692E6875000801034F7A656B69000101',
            '0605040B8423F0',
            45
        ))->toArray();

        self::assertSame('447700900000', $data['to']);
        self::assertSame('16105551212', $data['from']);
        self::assertSame('EA0601AE02056A0045C60C037761702E6F7A656B692E6875000801034F7A656B69000101', $data['body']);
        self::assertSame('0605040B8423F0', $data['udh']);
        self::assertSame(45, $data['protocol-id']);
    }
}
