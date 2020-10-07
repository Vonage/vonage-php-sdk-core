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
use Vonage\SMS\Message\WAPPush;

class WAPPushTest extends TestCase
{
    public function testCanCreateWAPMessage(): void
    {
        $data = (new WAPPush(
            '447700900000',
            '16105551212',
            'Check In Now!',
            'https://test.domain/check-in',
            300000
        ))->toArray();

        self::assertSame('447700900000', $data['to']);
        self::assertSame('16105551212', $data['from']);
        self::assertSame('Check In Now!', $data['title']);
        self::assertSame('https://test.domain/check-in', $data['url']);
        self::assertSame(300000, $data['validity']);
    }
}
