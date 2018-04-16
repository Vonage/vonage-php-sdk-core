<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2017 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Calls;

use Nexmo\Call\Transfer;
use EnricoStahn\JsonAssert\Assert as JsonAssert;
use PHPUnit\Framework\TestCase;

class TransferTest extends TestCase
{
    use JsonAssert;

    public function testStructureWithArray()
    {
        $transfer = new Transfer([
            'http://example.com',
            'http://alternate.example.com'
        ]);

        $json = json_decode(json_encode($transfer));
        $this->assertJsonMatchesSchema(__DIR__ . '/schema/transfer.json', $json);
        $this->assertJsonValueEquals('transfer', 'action', $json);
        $this->assertJsonValueEquals('ncco', 'destination.type', $json);
        $this->assertJsonValueEquals([
            'http://example.com',
            'http://alternate.example.com'
        ], 'destination.url', $json);
    }
    
    public function testStructureWithString()
    {
        $transfer = new Transfer('http://example.com');

        $json = json_decode(json_encode($transfer));
        $this->assertJsonMatchesSchema(__DIR__ . '/schema/transfer.json', $json);
        $this->assertJsonValueEquals('transfer', 'action', $json);
        $this->assertJsonValueEquals('ncco', 'destination.type', $json);
        $this->assertJsonValueEquals(['http://example.com'], 'destination.url', $json);
    }
}
