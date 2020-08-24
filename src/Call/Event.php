<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2017 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Call;

/**
 * @deprecated Will be removed in a future releases
 */
class Event implements \ArrayAccess
{
    protected $data;

    public function __construct($data)
    {
        trigger_error(
            'Vonage\Call\Event is deprecated and will be removed in a future release',
            E_USER_DEPRECATED
        );

        if (!isset($data['uuid']) || !isset($data['message'])) {
            throw new \InvalidArgumentException('missing message or uuid');
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

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException('can not set properties directly');
    }

    public function offsetUnset($offset)
    {
        throw new \RuntimeException('can not set properties directly');
    }
}
