<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Webhook;

use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

use function is_null;
use function json_decode;
use function parse_str;

abstract class Factory
{
    abstract public static function createFromArray(array $data);

    public static function createFromJson(string $json)
    {
        $data = json_decode($json, true);

        if (is_null($data)) {
            throw new RuntimeException("Invalid JSON string detected for webhook transformation");
        }

        return static::createFromArray($data);
    }

    public static function createFromGlobals()
    {
        $request = ServerRequestFactory::fromGlobals();

        return static::createFromRequest($request);
    }

    public static function createFromRequest(ServerRequestInterface $request)
    {
        $params = [];

        switch ($request->getMethod()) {
            case 'GET':
                $params = $request->getQueryParams();
                // Fix "null" values coming in from GET requests
                foreach ($params as $key => $value) {
                    if ($value === 'null') {
                        $params[$key] = null;
                    }
                }
                break;
            case 'POST':
                $type = $request->getHeader('content-type');
                if (!isset($type[0]) || $type[0] === 'application/json') {
                    $params = json_decode($request->getBody()->getContents(), true);
                } else {
                    parse_str($request->getBody()->getContents(), $params);
                    // Fix "null" values coming in from URL encoded requests
                    foreach ($params as $key => $value) {
                        if ($value === 'null') {
                            $params[$key] = null;
                        }
                    }
                }
                break;
            default:
                throw new RuntimeException("Invalid method for incoming webhook");
        }

        return static::createFromArray($params);
    }
}
