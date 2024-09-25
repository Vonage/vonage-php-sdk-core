<?php

declare(strict_types=1);

namespace Vonage\Client\Callback;

use InvalidArgumentException;
use RuntimeException;

use function array_diff;
use function array_keys;
use function array_merge;
use function implode;
use function strtolower;

class Callback implements CallbackInterface
{
    public const ENV_ALL = 'all';
    public const ENV_POST = 'post';
    public const ENV_GET = 'get';

    protected array $expected = [];

    protected array $data;

    public function __construct(array $data)
    {
        $keys = array_keys($data);
        $missing = array_diff($this->expected, $keys);

        if ($missing) {
            throw new RuntimeException('missing expected callback keys: ' . implode(', ', $missing));
        }

        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public static function fromEnv(string $source = self::ENV_ALL): callable|Callback
    {
        $data = match (strtolower($source)) {
            'post' => $_POST,
            'get' => $_GET,
            'all' => array_merge($_GET, $_POST),
            default => throw new InvalidArgumentException('invalid source: ' . $source),
        };

        return new static($data);
    }
}
