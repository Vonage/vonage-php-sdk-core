<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Call;

use ArrayAccess;
use InvalidArgumentException;
use RuntimeException;

/**
 * @deprecated Will be removed in a future releases
 */
class Event implements ArrayAccess
{
    protected $data;

    /**
     * Event constructor.
     *
     * @param $data
     */
    public function __construct($data)
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

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->data['uuid'];
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->data['message'];
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        throw new RuntimeException('can not set properties directly');
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        throw new RuntimeException('can not set properties directly');
    }
}
