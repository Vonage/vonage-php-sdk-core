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
use Vonage\Network\Number\Request;

class RequestTest extends TestCase
{
    public function testNullValuesNotPresent(): void
    {
        $request = new Request('14443332121', 'http://example.com');
        $params = $request->getParams();

        self::assertCount(2, $params);
        self::assertArrayHasKey('number', $params);
        self::assertArrayHasKey('callback', $params);
    }

    public function testNumberMatchesParams(): void
    {
        $request = new Request('14443332121', 'http://example.com');
        $params = $request->getParams();

        self::assertArrayHasKey('number', $params);
        self::assertEquals('14443332121', $params['number']);
    }

    public function testCallbackMatchesParams(): void
    {
        $request = new Request('14443332121', 'http://example.com');
        $params = $request->getParams();

        self::assertArrayHasKey('callback', $params);
        self::assertEquals('http://example.com', $params['callback']);
    }

    public function testFeaturesMatchesParams(): void
    {
        $request = new Request(
            '14443332121',
            'http://example.com',
            [Request::FEATURE_CARRIER, Request::FEATURE_PORTED]
        );
        $params = $request->getParams();

        self::assertArrayHasKey('features', $params);
        self::assertIsString($params['features']);

        $array = explode(',', $params['features']);

        self::assertCount(2, $array);
        self::assertContains(Request::FEATURE_CARRIER, $array);
        self::assertContains(Request::FEATURE_PORTED, $array);
    }

    public function testCallbackTimeoutMatchesParams(): void
    {
        $request = new Request(
            '14443332121',
            'http://example.com',
            [],
            100
        );
        $params = $request->getParams();

        self::assertArrayHasKey('callback_timeout', $params);
        self::assertEquals(100, $params['callback_timeout']);
    }

    public function testCallbackMethodMatchesParams(): void
    {
        $request = new Request(
            '14443332121',
            'http://example.com',
            [],
            null,
            'POST'
        );
        $params = $request->getParams();

        self::assertArrayHasKey('callback_method', $params);
        self::assertEquals('POST', $params['callback_method']);
    }

    public function testRefMatchesParams(): void
    {
        $request = new Request(
            '14443332121',
            'http://example.com',
            [],
            null,
            null,
            'ref'
        );
        $params = $request->getParams();

        self::assertArrayHasKey('client_ref', $params);
        self::assertEquals('ref', $params['client_ref']);
    }
}
