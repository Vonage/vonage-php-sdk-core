<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Call;

use ArrayAccess;
use InvalidArgumentException;
use RuntimeException;

use function trigger_error;

/**
 * @deprecated Will be removed in a future releases
 */
class Event implements ArrayAccess
{
    /**
     * @var array
     */
    protected $data = [];

    public function __construct(array $data)
    {
        trigger_error(
            'Vonage\Call\Event is deprecated and will be removed in a future release',
            E_USER_DEPRECATED
        );

        if (!isset($data['uuid'], $data['message'])) {
            throw new InvalidArgumentException('missing message or uuid');
        }

        $this->data = $data;
    }

    public function getId()
    {
        return $this->data['uuid'];
    }

    public function getMessage()
    {
        return $this->data['message'];
    }

    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        throw new RuntimeException('can not set properties directly');
    }

    public function offsetUnset($offset): void
    {
        throw new RuntimeException('can not set properties directly');
    }
}
