<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Voice\Message;

use PHPUnit\Framework\TestCase;
use Vonage\Voice\Message\Message;

class MessageTest extends TestCase
{
    /**
     * @var Message
     */
    protected $message;

    /**
     * @var string
     */
    protected $text = 'TTS Text';

    /**
     * @var string
     */
    protected $to = '15553331212';

    /**
     * @var string
     */
    protected $from = '15554441212';

    public function setUp(): void
    {
        $this->message = new Message($this->text, $this->to, $this->from);
    }

    public function testConstructorParams(): void
    {
        $params = $this->message->getParams();

        $this->assertArrayHasKey('text', $params);
        $this->assertArrayHasKey('to', $params);
        $this->assertArrayHasKey('from', $params);
        $this->assertEquals($this->text, $params['text']);
        $this->assertEquals($this->to, $params['to']);
        $this->assertEquals($this->from, $params['from']);
    }

    public function testFromIsOptional(): void
    {
        $message = new Message($this->text, $this->to);
        $params = $message->getParams();

        $this->assertArrayNotHasKey('from', $params);
    }

    public function testCallback(): void
    {
        $this->message->setCallback('http://example.com');
        $params = $this->message->getParams();

        $this->assertArrayHasKey('callback', $params);
        $this->assertEquals('http://example.com', $params['callback']);
        $this->assertArrayNotHasKey('callback_method', $params);

        $this->message->setCallback('http://example.com', 'POST');
        $params = $this->message->getParams();

        $this->assertArrayHasKey('callback', $params);
        $this->assertEquals('http://example.com', $params['callback']);
        $this->assertArrayHasKey('callback_method', $params);
        $this->assertEquals('POST', $params['callback_method']);

        $this->message->setCallback('http://example.com');
        $params = $this->message->getParams();

        $this->assertArrayHasKey('callback', $params);
        $this->assertEquals('http://example.com', $params['callback']);
        $this->assertArrayNotHasKey('callback_method', $params);
    }

    public function testMachine(): void
    {
        $this->message->setMachineDetection();
        $params = $this->message->getParams();

        $this->assertArrayHasKey('machine_detection', $params);
        $this->assertArrayNotHasKey('machine_timeout', $params);
        $this->assertEquals('hangup', $params['machine_detection']);

        $this->message->setMachineDetection(true, 100);
        $params = $this->message->getParams();

        $this->assertArrayHasKey('machine_detection', $params);
        $this->assertArrayHasKey('machine_timeout', $params);
        $this->assertEquals('hangup', $params['machine_detection']);
        $this->assertEquals(100, $params['machine_timeout']);

        $this->message->setMachineDetection(false);
        $params = $this->message->getParams();

        $this->assertArrayHasKey('machine_detection', $params);
        $this->assertArrayNotHasKey('machine_timeout', $params);
        $this->assertEquals('true', $params['machine_detection']);
    }

    /**
     * @dataProvider optionalParams
     *
     * @param $setter
     * @param $param
     * @param $values
     */
    public function testOptionalParams($setter, $param, $values): void
    {
        //check no default value
        $params = $this->message->getParams();
        $this->assertArrayNotHasKey($param, $params);

        //test values
        foreach ($values as $value => $expected) {
            $this->message->$setter($value);
            $params = $this->message->getParams();

            $this->assertArrayHasKey($param, $params);
            $this->assertEquals($expected, $params[$param]);
        }
    }

    /**
     * @return array[]
     */
    public function optionalParams(): array
    {
        return [
            ['setLanguage', 'lg', ['test' => 'test']],
            ['setVoice', 'voice', ['test' => 'test']],
            ['setRepeat', 'repeat', [2 => 2]],
        ];
    }
}
