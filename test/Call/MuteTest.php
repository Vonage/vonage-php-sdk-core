<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2017 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Calls;

use Vonage\Call\Mute;
use EnricoStahn\JsonAssert\Assert as JsonAssert;
use PHPUnit\Framework\TestCase;

class MuteTest extends TestCase
{
    use JsonAssert;

    public function testStructure()
    {
        $mute = @new Mute();

        $json = json_decode(json_encode($mute));
        $this->assertJsonMatchesSchema($json, __DIR__ . '/schema/mute.json');
        $this->assertJsonValueEquals('mute', 'action', $json);
    }
}
