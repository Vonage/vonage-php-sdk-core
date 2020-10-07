<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test\Message;

use PHPUnit\Framework\TestCase;
use Vonage\Message\AutoDetect;

class AutoDetectTest extends TestCase
{
    /**
     * When creating a message, it should not auto-detect encoding by default
     */
    public function testAutoDetectEnabledByDefault(): void
    {
        $message = new AutoDetect('to', 'from', 'Example Message');

        self::assertTrue($message->isEncodingDetectionEnabled());
    }
}
