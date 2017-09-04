<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2017 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Calls;

use Nexmo\Call\Unmute;
use EnricoStahn\JsonAssert\Assert as JsonAssert;

class UnmuteTest extends \PHPUnit_Framework_TestCase
{
    use JsonAssert;

    public function testStructure()
    {
        $mute = new Unmute();

        $json = json_decode(json_encode($mute));
        $this->assertJsonMatchesSchema(__DIR__ . '/schema/unmute.json', $json);
        $this->assertJsonValueEquals('unmute', 'action', $json);
    }
}
