<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Network\Number;

use PHPUnit\Framework\TestCase;
use Vonage\Network\Number\Callback;

class CallbackTest extends TestCase
{
    protected $data = [
        'request_id' => '12345',
        'callback_total_parts' => 2,
        'callback_part' => 2,
        'number' => '14443332121',
        'status' => 0
    ];

    /**
     * @var Callback
     */
    protected $callback;

    public function setup(): void
    {
        $this->callback = new Callback($this->data);
    }

    public function testMethodsMatchData(): void
    {
        self::assertEquals($this->data['request_id'], $this->callback->getId());
        self::assertEquals($this->data['callback_total_parts'], $this->callback->getCallbackTotal());
        self::assertEquals($this->data['callback_part'], $this->callback->getCallbackIndex());
        self::assertEquals($this->data['number'], $this->callback->getNumber());
    }

    /**
     * @dataProvider optionalData
     * @param $key
     * @param $value
     * @param $method
     * @param $expected
     */
    public function testOptionalData($key, $value, $method, $expected): void
    {
        $has = 'has' . $method;
        $get = 'get' . $method;

        self::assertFalse($this->callback->$has());
        self::assertNull($this->callback->$get());

        $callback = new Callback(array_merge($this->data, [$key => $value]));

        self::assertTrue($callback->$has());
        self::assertEquals($expected, $callback->$get());
    }

    public function optionalData(): array
    {
        return [
            ['number_type', 'unknown', 'Type', 'unknown'],
            ['carrier_network_code', 'CODE', 'Network', 'CODE'],
            ['carrier_network_name', 'NAME', 'NetworkName', 'NAME'],
            ['valid', 'unknown', 'Valid', 'unknown'],
            ['ported', 'unknown', 'Ported', 'unknown'],
            ['reachable', 'unknown', 'Reachable', 'unknown'],
            ['roaming', 'unknown', 'Roaming', 'unknown'],
            ['roaming_country_code', 'CODE', 'RoamingCountry', 'CODE'],
            ['roaming_network_code', 'CODE', 'RoamingNetwork', 'CODE'],
        ];
    }
}
