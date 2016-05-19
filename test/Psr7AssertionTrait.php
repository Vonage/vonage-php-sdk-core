<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest;

use Psr\Http\Message\RequestInterface;

trait Psr7AssertionTrait
{
    public static function assertRequestQueryContains($key, $value, RequestInterface $request)
    {
        $query = $request->getUri()->getQuery();
        $params = [];
        parse_str($query, $params);
        self::assertArrayHasKey($key, $params, 'query string does not have key: ' . $key);
        self::assertSame($value, $params[$key], 'query string does not have value: ' . $value);
    }

    public static function assertRequestQueryHas($key, RequestInterface $request)
    {
        $query = $request->getUri()->getQuery();
        $params = [];
        parse_str($query, $params);
        self::assertArrayHasKey($key, $params, 'query string does not have key: ' . $key);
    }

    public static function assertRequestFormBodyContains($key, $value, RequestInterface $request)
    {
        self::assertEquals('application/x-www-form-urlencoded', $request->getHeaderLine('content-type'), 'incorrect `Content-Type` for POST body');
        $request->getBody()->rewind();
        $data = $request->getBody()->getContents();
        $params = [];
        parse_str($data, $params);
        self::assertArrayHasKey($key, $params, 'body does not have key: ' . $key);
        self::assertSame($value, $params[$key], 'body does not have value: ' . $value);
    }

    public static function assertRequestJsonBodyContains($key, $value, RequestInterface $request)
    {
        self::assertEquals('application/json', $request->getHeaderLine('content-type'), 'incorrect `Content-Type` for JSON body');
        $request->getBody()->rewind();
        $params = json_decode($request->getBody()->getContents(), true);
        self::assertArrayHasKey($key, $params, 'body does not have key: ' . $key);
        self::assertSame($value, $params[$key], 'body does not have value: ' . $value);
    }

    public static function assertRequestMatchesUrl($url, RequestInterface $request)
    {
        self::assertEquals($url, $request->getUri()->withQuery('')->__toString(), 'url did not match request');
    }
}