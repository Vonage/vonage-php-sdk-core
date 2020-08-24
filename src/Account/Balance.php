<?php

namespace Vonage\Account;

use ArrayAccess;
use Vonage\Client\Exception\Exception;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Entity\JsonSerializableInterface;
use Vonage\Entity\JsonUnserializableInterface;

/**
 * This class will no longer be accessible via array keys past v2
 * @todo Have the JSON unserialize/serialize keys match with $this->data keys
 */
class Balance implements
    \JsonSerializable,
    JsonSerializableInterface,
    JsonUnserializableInterface,
    ArrayAccess,
    ArrayHydrateInterface
{
    /**
     * @var array
     */
    protected $data;

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
        return $this->data['balance'];
    }

    public function getAutoReload()
    {
        return $this->data['auto_reload'];
    }

    public function jsonUnserialize(array $json)
    {
        trigger_error(
            get_class($this) . "::jsonUnserialize is deprecated, please fromArray() instead",
            E_USER_DEPRECATED
        );
        $this->fromArray($json);
    }

    public function jsonSerialize()
    {
        return $this->data;
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
        throw new Exception('Balance is read only');
    }

    public function offsetUnset($offset)
    {
        throw new Exception('Balance is read only');
    }

    public function fromArray(array $data)
    {
        $this->data = [
            'balance' => $data['value'],
            'auto_reload' => $data['autoReload']
        ];
    }

    public function toArray(): array
    {
        return $this->data;
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
