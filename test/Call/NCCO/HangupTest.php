<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2017 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Calls;

use Nexmo\Call\NCCO\Hangup;
use EnricoStahn\JsonAssert\Assert as JsonAssert;
use PHPUnit\Framework\TestCase;

class HangupTest extends TestCase
{
    use JsonAssert;

    public function testStructure()
    {
        $hangup = new Hangup();

        $json = json_decode(json_encode($hangup));
        $this->assertJsonMatchesSchema($json, __DIR__ . '/../schema/hangup.json');
        $this->assertJsonValueEquals('hangup', 'action', $json);
    }
}
