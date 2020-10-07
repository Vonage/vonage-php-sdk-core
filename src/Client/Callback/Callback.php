<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Client\Callback;

use InvalidArgumentException;
use RuntimeException;

class Callback implements CallbackInterface
{
    public const ENV_ALL = 'all';
    public const ENV_POST = 'post';
    public const ENV_GET = 'get';

    protected $expected = [];
    protected $data;

    /**
     * Callback constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $keys = array_keys($data);
        $missing = array_diff($this->expected, $keys);

        if ($missing) {
            throw new RuntimeException('missing expected callback keys: ' . implode(', ', $missing));
        }

        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param string $source
     * @return Callback|callable
     */
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
                throw new InvalidArgumentException('invalid source: ' . $source);
        }

        return new static($data);
    }
}
