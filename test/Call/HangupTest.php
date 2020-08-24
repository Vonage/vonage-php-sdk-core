<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2017 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Calls;

use Vonage\Call\Hangup;
use EnricoStahn\JsonAssert\Assert as JsonAssert;
use PHPUnit\Framework\TestCase;

class HangupTest extends TestCase
{
    use JsonAssert;

    public function testStructure()
    {
        $hangup = @new Hangup();

        $json = json_decode(json_encode($hangup));
        $this->assertJsonMatchesSchema($json, __DIR__ . '/schema/hangup.json');
        $this->assertJsonValueEquals('hangup', 'action', $json);
    }
}
