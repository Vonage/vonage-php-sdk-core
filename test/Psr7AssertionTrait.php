<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

trait Psr7AssertionTrait
{
    public static function assertRequestMethod($expected, RequestInterface $request)
    {
        self::assertEquals($expected, $request->getMethod());
    }

    public static function assertRequestBodyIsEmpty(RequestInterface $request)
    {
        $request->getBody()->rewind();
        $body = $request->getBody()->getContents();
        $request->getBody()->rewind();
        self::assertEmpty($body);
    }

    public static function assertRequestBodyIsJson($expected, RequestInterface $request)
    {
        $request->getBody()->rewind();
        $body = $request->getBody()->getContents();
        $request->getBody()->rewind();

        self::assertJsonStringEqualsJsonString($expected, $body);
    }

    public static function assertRequestUrl($host, $path, $method, RequestInterface $request)
    {
        self::assertEquals($host,   $request->getUri()->getHost());
        self::assertEquals($path,   $request->getUri()->getPath());
        self::assertEquals($method, $request->getMethod());
    }

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
        self::assertSame($value, $params[$key]);
    }

    public static function assertRequestMatchesUrl($url, RequestInterface $request)
    {
        self::assertEquals($url, $request->getUri()->withQuery('')->__toString(), 'url did not match request');
    }

    public static function assertRequestMatchesUrlWithQueryString($url, RequestInterface $request)
    {
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        unset($query['api_key'], $query['api_secret']);
        $query = http_build_query($query);
        self::assertEquals($url, $request->getUri()->withQuery($query)->__toString(), 'url did not match request');
    }
}