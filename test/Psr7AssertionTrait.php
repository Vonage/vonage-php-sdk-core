<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest;

use PHPUnit\Framework\ExpectationFailedException;
use Psr\Http\Message\RequestInterface;

use function http_build_query;
use function is_array;
use function json_decode;
use function json_encode;
use function parse_str;

trait Psr7AssertionTrait
{
    /**
     * @param $expected
     * @param RequestInterface $request
     */
    public static function assertRequestMethod($expected, RequestInterface $request): void
    {
        self::assertEquals($expected, $request->getMethod());
    }

    public static function assertRequestBodyIsEmpty(RequestInterface $request): void
    {
        $request->getBody()->rewind();
        $body = $request->getBody()->getContents();
        $request->getBody()->rewind();

        self::assertEmpty($body);
    }

    /**
     * @param $expected
     * @param RequestInterface $request
     */
    public static function assertRequestBodyIsJson($expected, RequestInterface $request): void
    {
        $request->getBody()->rewind();
        $body = $request->getBody()->getContents();
        $request->getBody()->rewind();

        self::assertJsonStringEqualsJsonString($expected, $body);
    }

    /**
     * @param $host
     * @param $path
     * @param $method
     * @param RequestInterface $request
     */
    public static function assertRequestUrl($host, $path, $method, RequestInterface $request): void
    {
        self::assertEquals($host, $request->getUri()->getHost());
        self::assertEquals($path, $request->getUri()->getPath());
        self::assertEquals($method, $request->getMethod());
    }

    /**
     * @param $key
     * @param RequestInterface $request
     */
    public static function assertRequestQueryNotContains($key, RequestInterface $request): void
    {
        $query = $request->getUri()->getQuery();
        $params = [];
        parse_str($query, $params);

        self::assertArrayNotHasKey($key, $params, 'query string has key when it should not: ' . $key);
    }

    /**
     * @param $key
     * @param $value
     * @param RequestInterface $request
     */
    public static function assertRequestQueryContains($key, $value, RequestInterface $request): void
    {
        $query = $request->getUri()->getQuery();
        $params = [];
        parse_str($query, $params);

        self::assertArrayHasKey($key, $params, 'query string does not have key: ' . $key);

        $errorValue = $value;

        if (is_array($errorValue)) {
            $errorValue = json_encode($errorValue);
        }

        self::assertSame($value, $params[$key], 'query string does not have value: ' . $errorValue);
    }

    /**
     * @param $key
     * @param RequestInterface $request
     */
    public static function assertRequestQueryHas($key, RequestInterface $request): void
    {
        $query = $request->getUri()->getQuery();
        $params = [];
        parse_str($query, $params);
        self::assertArrayHasKey($key, $params, 'query string does not have key: ' . $key);
    }

    /**
     * @param $key
     * @param $value
     * @param RequestInterface $request
     */
    public static function assertRequestFormBodyContains($key, $value, RequestInterface $request): void
    {
        self::assertEquals(
            'application/x-www-form-urlencoded',
            $request->getHeaderLine('content-type'),
            'incorrect `Content-Type` for POST body'
        );

        $request->getBody()->rewind();
        $data = $request->getBody()->getContents();
        $params = [];
        parse_str($data, $params);

        self::assertArrayHasKey($key, $params, 'body does not have key: ' . $key);
        self::assertSame($value, $params[$key], 'body does not have value: ' . $value);
    }

    /**
     * @param $key
     * @param $value
     * @param RequestInterface $request
     */
    public static function assertRequestJsonBodyContains(
        $key,
        $value,
        RequestInterface $request,
        bool $nested = false
    ): void {
        self::assertEquals(
            'application/json',
            $request->getHeaderLine('content-type'),
            'incorrect `Content-Type` for JSON body'
        );

        $request->getBody()->rewind();
        $params = json_decode($request->getBody()->getContents(), true);

        if (!$nested) {
            self::assertArrayHasKey($key, $params, 'body does not have key: ' . $key);
            self::assertSame($value, $params[$key]);

            return;
        }

        try {
            $keyValue = self::findNestedKey($params, $key, $value);
            self::assertSame($value, $keyValue);
        } catch (\OutOfBoundsException $e) {
            throw new ExpectationFailedException('Body does not nested have key: ' . $key);
        }
    }

    protected static function findNestedKey(array $params, string $searchKey, mixed $searchValue)
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($params));

        while ($iterator->valid()) {
            if ($iterator->key() === $searchKey && $iterator->current() === $searchValue) {
                return $iterator->current();
            }

            $iterator->next();
        }

        throw new \OutOfBoundsException('Cannot find given Key');
    }

    /**
     * @param $key
     * @param $value
     * @param RequestInterface $request
     */
    public static function assertRequestJsonBodyMissing($key, RequestInterface $request): void
    {
        self::assertEquals(
            'application/json',
            $request->getHeaderLine('content-type'),
            'incorrect `Content-Type` for JSON body'
        );

        $request->getBody()->rewind();
        $params = json_decode($request->getBody()->getContents(), true);

        self::assertArrayNotHasKey($key, $params, 'body does not have key: ' . $key);
    }

    /**
     * @param $url
     * @param RequestInterface $request
     */
    public static function assertRequestMatchesUrl($url, RequestInterface $request): void
    {
        self::assertEquals($url, $request->getUri()->withQuery('')->__toString(), 'url did not match request');
    }

    /**
     * @param $url
     * @param RequestInterface $request
     */
    public static function assertRequestMatchesUrlWithQueryString($url, RequestInterface $request): void
    {
        $query = [];

        parse_str($request->getUri()->getQuery(), $query);

        unset($query['api_key'], $query['api_secret']);

        $query = http_build_query($query);

        self::assertEquals($url, $request->getUri()->withQuery($query)->__toString(), 'url did not match request');
    }
}
