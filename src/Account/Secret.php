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
        return $this['id'];
    }

    public function getCreatedAt()
    {
        return $this['created_at'];
    }

    public function getLinks()
    {
        return $this['_links'];
    }

    /**
     * @deprecated Instatiate the object directly
     */
    public static function fromApi($data)
    {
        trigger_error('Please instatiate a Nexmo\Account\Secret object instead of using fromApi', E_USER_DEPRECATED);
        return new self($data);
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
        throw new \Exception('Secret::offsetSet is not implemented');
    }

    public function offsetUnset($offset)
    {
        throw new \Exception('Secret::offsetUnset is not implemented');
    }
}
