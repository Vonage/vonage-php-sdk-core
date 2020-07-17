<?php
declare(strict_types=1);

namespace Nexmo\Webhook;

use PDO;
use Zend\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface;

abstract class Factory
{
    abstract public static function createFromArray(array $data);

    public static function createFromJson(string $json)
    {
        $data = json_decode($json, true);

        if (is_null($data)) {
            throw new \RuntimeException("Invalid JSON string detected for webhook transformation");
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
                break;
            case 'POST':
                $type = $request->getHeader('content-type');
                if (!isset($type[0]) || $type[0] === 'application/json') {
                    $params = json_decode($request->getBody()->getContents(), true);
                } else {
                    parse_str($request->getBody()->getContents(), $params);
                }
                break;
            default:
                throw new \RuntimeException("Invalid method for incoming webhook");
        }

        return static::createFromArray($params);
    }
}
