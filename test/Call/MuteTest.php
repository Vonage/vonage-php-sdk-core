<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2017 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Calls;

use Nexmo\Call\Mute;
use EnricoStahn\JsonAssert\Assert as JsonAssert;

class MuteTest extends \PHPUnit_Framework_TestCase
{
    use JsonAssert;

    public function testStructure()
    {
        $mute = new Mute();

        $json = json_decode(json_encode($mute));
        $this->assertJsonMatchesSchema(__DIR__ . '/schema/mute.json', $json);
        $this->assertJsonValueEquals('mute', 'action', $json);
    }
}
