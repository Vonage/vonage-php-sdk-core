<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Voice\Message;

use DateTime;
use PHPUnit\Framework\TestCase;
use Vonage\Voice\Message\Callback;

class CallbackTest extends TestCase
{
    protected $data = [
        'call-id' => '1234abcd',
        'status' => 'ok',
        'call-price' => '.0012',
        'call-rate' => '.012',
        'call-duration' => '10',
        'to' => '15553332323',
        'call-request' => '2014-01-01 10:30:15',
        'network-code' => '1234',
        'call-start' => '2014-01-01 10:30:25',
        'call-end' => '2014-01-01 10:30:35'
    ];

    /**
     * @var Callback
     */
    protected $callback;

    public function setUp(): void
    {
        $this->callback = new Callback($this->data);
    }

    public function testSimpleValues(): void
    {
        $this->assertEquals($this->data['call-id'], $this->callback->getId());
        $this->assertEquals($this->data['status'], $this->callback->getStatus());
        $this->assertEquals($this->data['call-price'], $this->callback->getPrice());
        $this->assertEquals($this->data['call-rate'], $this->callback->getRate());
        $this->assertEquals($this->data['call-duration'], $this->callback->getDuration());
        $this->assertEquals($this->data['to'], $this->callback->getTo());
        $this->assertEquals($this->data['network-code'], $this->callback->getNetwork());
    }

    public function testStartAndEndOptional(): void
    {
        unset($this->data['call-start'], $this->data['call-end']);

        $this->callback = new Callback($this->data);

        $this->assertNull($this->callback->getStart());
        $this->assertNull($this->callback->getEnd());
    }

    public function testDateValues(): void
    {
        $this->assertEquals(new DateTime('2014-01-01 10:30:15'), $this->callback->getCreated());
        $this->assertEquals(new DateTime('2014-01-01 10:30:25'), $this->callback->getStart());
        $this->assertEquals(new DateTime('2014-01-01 10:30:35'), $this->callback->getEnd());
    }
}
