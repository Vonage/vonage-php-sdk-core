<?php

namespace Nexmo\Account;

use Nexmo\Entity\Hydrator\ArrayHydrateInterface;
use Nexmo\InvalidResponseException;

class Secret implements \ArrayAccess
{
    protected $data;

    public function __construct($data)
    {
        if (!isset($data['id'])) {
            throw new InvalidResponseException("Missing key: 'id");
        }
        if (!isset($data['created_at'])) {
            throw new InvalidResponseException("Missing key: 'created_at");
        }

        $this->data = $data;
    }

    public function getId()
    {
        return $this->data['id'];
    }

    public function getCreatedAt()
    {
        return $this->data['created_at'];
    }

    public function getLinks()
    {
        return $this->data['_links'];
    }

    public function offsetExists($offset)
    {
        trigger_error(
            "Array access for " . get_class($this) . " is deprecated, please use getter methods",
            E_USER_DEPRECATED
        );
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        trigger_error(
            "Array access for " . get_class($this) . " is deprecated, please use getter methods",
            E_USER_DEPRECATED
        );
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new \Exception('Secret::offsetSet is not implemented');
    }

    public function offsetUnset($offset)
    {
        throw new \Exception('Secret::offsetUnset is not implemented');
    }

    public function __get($key)
    {
        if ($key === 'data') {
            trigger_error(
                "Direct access to " . get_class($this) . "::data is deprecated, please use getter to toArray() methods",
                E_USER_DEPRECATED
            );
            return $this->data;
        }
    }
}
