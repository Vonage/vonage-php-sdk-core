<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Calls\Update;

use Nexmo\Calls\Update\Hangup;
use EnricoStahn\JsonAssert\Assert as JsonAssert;

class HangupTest extends \PHPUnit_Framework_TestCase
{
    use JsonAssert;

    public function testStructure()
    {
        $hangup = new Hangup();

        $json = json_decode(json_encode($hangup));
        $this->assertJsonMatchesSchema(__DIR__ . '/schema/hangup.json', $json);
        $this->assertJsonValueEquals('hangup', 'action', $json);
    }
}
