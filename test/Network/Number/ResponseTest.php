<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Network\Number;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use Vonage\Network\Number\Callback;
use Vonage\Network\Number\Response;

class ResponseTest extends TestCase
{
    protected $data = [
        'request_id' => '12345',
        'number' => '14443332121',
        'remaining_balance' => 123.45,
        'request_price' => 0.05,
        'callback_total_parts' => 2,
        'status' => 0,
    ];

    /**
     * @var Response;
     */
    protected $response;

    public function setUp(): void
    {
        $this->response = new Response($this->data);
    }

    public function testMethodsMatchData(): void
    {
        $this->assertEquals($this->data['request_id'], $this->response->getId());
        $this->assertEquals($this->data['number'], $this->response->getNumber());
        $this->assertEquals($this->data['request_price'], $this->response->getPrice());
        $this->assertEquals($this->data['remaining_balance'], $this->response->getBalance());
        $this->assertEquals($this->data['callback_total_parts'], $this->response->getCallbackTotal());
        $this->assertEquals($this->data['status'], $this->response->getStatus());
    }

    /**
     * @dataProvider getOptionalProperties
     *
     * @param $property
     */
    public function testCantGetOptionalDataBeforeCallback($property): void
    {
        $this->expectException(BadMethodCallException::class);

        $get = 'get' . $property;
        $this->response->$get();
    }

    /**
     * @dataProvider getOptionalProperties
     *
     * @param $property
     */
    public function testCantHasOptionalDataBeforeCallback($property): void
    {
        $this->expectException(BadMethodCallException::class);

        $has = 'has' . $property;
        $this->response->$has();
    }

    /**
     * Test that any optional parameters are simply passed to the callback stack (when there is at least one), until the
     * value is found (or return the last callback's data).
     *
     * @dataProvider getOptionalProperties
     *
     * @param $property
     */
    public function testOptionalDataProxiesCallback($property): void
    {
        $has = 'has' . $property;
        $get = 'get' . $property;

        $callback = $this->getMockBuilder(Callback::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', $has, $get])
            ->getMock();

        //setup so the request will accept the callback
        $callback
            ->method('getId')
            ->willReturn($this->data['request_id']);

        $callback->expects(self::atLeastOnce())
            ->method($has)
            ->willReturnCallback(function () {
                static $called = false;
                if (!$called) {
                    $called = true;
                    return false;
                }

                return true;
            });

        $callback->expects(self::atLeastOnce())
            ->method($get)
            ->willReturnCallback(function () {
                static $called = false;
                if (!$called) {
                    $called = true;
                    return null;
                }

                return 'data';
            });

        $response = new Response($this->data, [$callback, $callback]);

        $this->assertTrue($response->$has());
        $this->assertEquals('data', $response->$get());
    }

    public function getOptionalProperties(): array
    {
        return [
            ['Type'],
            ['Network'],
            ['NetworkName'],
            ['Valid'],
            ['Ported'],
            ['Reachable'],
            ['Roaming'],
            ['RoamingCountry'],
            ['RoamingNetwork'],
        ];
    }
}
