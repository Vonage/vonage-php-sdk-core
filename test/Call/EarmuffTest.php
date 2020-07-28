<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2017 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Calls;

use Nexmo\Call\Earmuff;
use EnricoStahn\JsonAssert\Assert as JsonAssert;
use PHPUnit\Framework\TestCase;

class EarmuffTest extends TestCase
{
    use JsonAssert;

    public function testStructure()
    {
        $mute = @new Earmuff();

        $json = json_decode(json_encode($mute));
        $this->assertJsonMatchesSchema($json, __DIR__ . '/schema/earmuff.json');
        $this->assertJsonValueEquals('earmuff', 'action', $json);
    }
}
