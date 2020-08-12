<?php

namespace Vonage;

use ArrayAccess;
use Vonage\Client\Exception\Exception;
use Vonage\Entity\EntityInterface;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Entity\JsonSerializableInterface;
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\JsonSerializableTrait;
use Vonage\Entity\NoRequestResponseTrait;
use Vonage\Entity\JsonUnserializableInterface;

/**
 * This class will no longer be accessible via array access, nor contain request/response information after v2.
 */
class Network implements
    EntityInterface,
    \JsonSerializable,
    JsonSerializableInterface,
    JsonUnserializableInterface,
    ArrayAccess,
    ArrayHydrateInterface
{
    use JsonSerializableTrait;
    use NoRequestResponseTrait;
    use JsonResponseTrait;

    protected $data = [];

    public function __construct($networkCode, $networkName)
    {
        $this->data['network_code'] = $networkCode;
        $this->data['network_name'] = $networkName;
    }

    public function getCode()
    {
        return $this->data['network_code'];
    }

    public function getName()
    {
        return $this->data['network_name'];
    }

    public function getOutboundSmsPrice()
    {
        if (isset($this->data['sms_price'])) {
            return $this->data['sms_price'];
        }
        return $this->data['price'];
    }

    public function getOutboundVoicePrice()
    {
        if (isset($this->data['voice_price'])) {
            return $this->data['voice_price'];
        }
        return $this->data['price'];
    }

    public function getPrefixPrice()
    {
        return $this->data['mt_price'];
    }

    public function getCurrency()
    {
        return $this->data['currency'];
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
        return $this->toArray();
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
        throw new Exception('Network is read only');
    }

    public function offsetUnset($offset)
    {
        throw new Exception('Network is read only');
    }

    public function fromArray(array $data) : void
    {
        // Convert CamelCase to snake_case as that's how we use array access in every other object
        $storage = [];
        foreach ($data as $k => $v) {
            $k = ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $k)), '_');
            $storage[$k] = $v;
        }
        $this->data = $storage;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
