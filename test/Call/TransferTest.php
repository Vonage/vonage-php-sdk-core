<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2017 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Calls;

use Vonage\Call\Transfer;
use EnricoStahn\JsonAssert\Assert as JsonAssert;
use PHPUnit\Framework\TestCase;

class TransferTest extends TestCase
{
    use JsonAssert;

    public function testStructureWithArray()
    {
        $transfer = @new Transfer([
            'http://example.com',
            'http://alternate.example.com'
        ]);

        $json = json_decode(json_encode($transfer));
        $this->assertJsonMatchesSchema($json, __DIR__ . '/schema/transfer.json');
        $this->assertJsonValueEquals('transfer', 'action', $json);
        $this->assertJsonValueEquals('ncco', 'destination.type', $json);
        $this->assertJsonValueEquals([
            'http://example.com',
            'http://alternate.example.com'
        ], 'destination.url', $json);
    }
    
    public function testStructureWithString()
    {
        $transfer = @new Transfer('http://example.com');

        $json = json_decode(json_encode($transfer));
        $this->assertJsonMatchesSchema($json, __DIR__ . '/schema/transfer.json');
        $this->assertJsonValueEquals('transfer', 'action', $json);
        $this->assertJsonValueEquals('ncco', 'destination.type', $json);
        $this->assertJsonValueEquals(['http://example.com'], 'destination.url', $json);
    }
}
