<?php

namespace Nexmo\Account;

use ArrayAccess;
use Nexmo\Client\Exception\Exception;
use Nexmo\Entity\JsonSerializableInterface;
use Nexmo\Entity\JsonUnserializableInterface;

/**
 * @todo Have the JSON unserialize/serialize keys match with $this->data keys
 */
class Balance implements JsonSerializableInterface, JsonUnserializableInterface, ArrayAccess
{
    /**
     * @var array
     */
    public $data;

    /**
     * @todo Have these take null values, since we offer an unserialize option to populate
     */
    public function __construct($balance, $autoReload)
    {
        $this->data['balance'] = $balance;
        $this->data['auto_reload'] = $autoReload;
    }

    public function getBalance()
    {
        return $this['balance'];
    }

    public function getAutoReload()
    {
        return $this['auto_reload'];
    }

    public function jsonUnserialize(array $json)
    {
        $this->data = [
            'balance' => $json['value'],
            'auto_reload' => $json['autoReload']
        ];
    }

    public function jsonSerialize()
    {
        return $this->data;
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
        throw new Exception('Balance is read only');
    }

    public function offsetUnset($offset)
    {
        throw new Exception('Balance is read only');
    }
}
