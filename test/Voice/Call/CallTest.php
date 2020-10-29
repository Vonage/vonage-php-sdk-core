<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Voice\Call;

use PHPUnit\Framework\TestCase;
use Vonage\Voice\Call\Call;

class CallTest extends TestCase
{
    /**
     * @var Call
     */
    protected $call;

    /**
     * @var string
     */
    protected $to = '15554443232';

    /**
     * @var string
     */
    protected $from = '15551112323';

    /**
     * @var string
     */
    protected $url = 'http://example.com';

    public function setUp(): void
    {
        $this->call = new Call($this->url, $this->to, $this->from);
    }

    public function testConstructParams(): void
    {
        $params = $this->call->getParams();

        self::assertArrayHasKey('to', $params);
        self::assertArrayHasKey('from', $params);
        self::assertArrayHasKey('answer_url', $params);
        self::assertEquals($this->to, $params['to']);
        self::assertEquals($this->from, $params['from']);
        self::assertEquals($this->url, $params['answer_url']);
    }

    public function testFromOptional(): void
    {
        self::assertArrayNotHasKey('from', (new Call($this->url, $this->to))->getParams());
    }

    public function testMachine(): void
    {
        $this->call->setMachineDetection();
        $params = $this->call->getParams();

        self::assertArrayHasKey('machine_detection', $params);
        self::assertArrayNotHasKey('machine_timeout', $params);
        self::assertEquals('hangup', $params['machine_detection']);

        $this->call->setMachineDetection(true, 100);
        $params = $this->call->getParams();

        self::assertArrayHasKey('machine_detection', $params);
        self::assertArrayHasKey('machine_timeout', $params);
        self::assertEquals('hangup', $params['machine_detection']);
        self::assertEquals(100, $params['machine_timeout']);

        $this->call->setMachineDetection(false);
        $params = $this->call->getParams();

        self::assertArrayHasKey('machine_detection', $params);
        self::assertArrayNotHasKey('machine_timeout', $params);
        self::assertEquals('true', $params['machine_detection']);
    }

    /**
     * @dataProvider getCallbacks
     * @param string $method
     * @param string $param
     * @param string $param_method
     */
    public function testCallback(string $method, string $param, string $param_method): void
    {
        $this->call->$method('http://example.com');
        $params = $this->call->getParams();

        self::assertArrayHasKey($param, $params);
        self::assertEquals('http://example.com', $params[$param]);
        self::assertArrayNotHasKey($param_method, $params);

        $this->call->$method('http://example.com', 'POST');
        $params = $this->call->getParams();

        self::assertArrayHasKey($param, $params);
        self::assertEquals('http://example.com', $params[$param]);
        self::assertArrayHasKey($param_method, $params);
        self::assertEquals('POST', $params[$param_method]);

        $this->call->$method('http://example.com');
        $params = $this->call->getParams();

        self::assertArrayHasKey($param, $params);
        self::assertEquals('http://example.com', $params[$param]);
        self::assertArrayNotHasKey($param_method, $params);
    }

    /**
     * @return string[]
     */
    public function getCallbacks(): array
    {
        return [
            ['setAnswer', 'answer_url', 'answer_method'],
            ['setError', 'error_url', 'error_method'],
            ['setStatus', 'status_url', 'status_method']
        ];
    }
}
