<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\Test\Voice\Message;

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
        self::assertEquals($this->data['call-id'], $this->callback->getId());
        self::assertEquals($this->data['status'], $this->callback->getStatus());
        self::assertEquals($this->data['call-price'], $this->callback->getPrice());
        self::assertEquals($this->data['call-rate'], $this->callback->getRate());
        self::assertEquals($this->data['call-duration'], $this->callback->getDuration());
        self::assertEquals($this->data['to'], $this->callback->getTo());
        self::assertEquals($this->data['network-code'], $this->callback->getNetwork());
    }

    public function testStartAndEndOptional(): void
    {
        unset($this->data['call-start'], $this->data['call-end']);

        $this->callback = new Callback($this->data);

        self::assertNull($this->callback->getStart());
        self::assertNull($this->callback->getEnd());
    }

    public function testDateValues(): void
    {
        self::assertEquals(new DateTime('2014-01-01 10:30:15'), $this->callback->getCreated());
        self::assertEquals(new DateTime('2014-01-01 10:30:25'), $this->callback->getStart());
        self::assertEquals(new DateTime('2014-01-01 10:30:35'), $this->callback->getEnd());
    }
}
