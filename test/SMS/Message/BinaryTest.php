<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\SMS\Message;

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

        $this->assertSame('447700900000', $data['to']);
        $this->assertSame('16105551212', $data['from']);
        $this->assertSame('EA0601AE02056A0045C60C037761702E6F7A656B692E6875000801034F7A656B69000101', $data['body']);
        $this->assertSame('0605040B8423F0', $data['udh']);
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

        $this->assertSame('447700900000', $data['to']);
        $this->assertSame('16105551212', $data['from']);
        $this->assertSame('EA0601AE02056A0045C60C037761702E6F7A656B692E6875000801034F7A656B69000101', $data['body']);
        $this->assertSame('0605040B8423F0', $data['udh']);
        $this->assertSame(45, $data['protocol-id']);
    }
}
