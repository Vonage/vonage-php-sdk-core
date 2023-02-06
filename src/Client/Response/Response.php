<?php

declare(strict_types=1);

namespace Vonage\Client\Response;

use RuntimeException;

use function array_diff;
use function array_keys;
use function implode;

class Response extends AbstractResponse
{
    /**
     * Allow specific responses to easily define required parameters.
     *
     * @var array
     */
    protected $expected = [];

    public function __construct(array $data)
    {
        $keys = array_keys($data);
        $missing = array_diff($this->expected, $keys);

        if ($missing) {
            throw new RuntimeException('missing expected response keys: ' . implode(', ', $missing));
        }

        $this->data = $data;
    }
}
