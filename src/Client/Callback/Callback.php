<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Client\Callback;

class Callback implements CallbackInterface
{
    const ENV_ALL =  'all';
    const ENV_POST = 'post';
    const ENV_GET  = 'get';

    protected $expected = array();
    protected $data;


    public function __construct(array $data)
    {
        $keys = array_keys($data);
        $missing = array_diff($this->expected, $keys);

        if ($missing) {
            throw new \RuntimeException('missing expected callback keys: ' . implode(', ', $missing));
        }

        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public static function fromEnv($source = self::ENV_ALL)
    {
        switch (strtolower($source)) {
            case 'post':
                $data = $_POST;
                break;
            case 'get':
                $data = $_GET;
                break;
            case 'all':
                $data = array_merge($_GET, $_POST);
                break;
            default:
                throw new \InvalidArgumentException('invalid source: ' . $source);
        }

        return new static($data);
    }
}
