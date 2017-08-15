<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Message;
use Nexmo\Message\AutoDetect;

class AutoDetectTest extends \PHPUnit_Framework_TestCase
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
