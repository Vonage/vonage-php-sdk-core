<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Message;

use Vonage\Message\AutoDetect;
use PHPUnit\Framework\TestCase;

class AutoDetectTest extends TestCase
{
    /**
     * When creating a message, it should not auto-detect encoding by default
     */
    public function testAutoDetectEnabledByDefault()
    {
        $message = new AutoDetect('to', 'from', 'Example Message');
        $this->assertTrue($message->isEncodingDetectionEnabled());
    }


}
