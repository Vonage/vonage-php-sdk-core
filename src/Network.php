<?php

namespace Nexmo;

use ArrayAccess;
use Nexmo\Client\Exception\Exception;
use Nexmo\Entity\EntityInterface;
use Nexmo\Entity\Hydrator\ArrayHydrateInterface;
use Nexmo\Entity\JsonSerializableInterface;
use Nexmo\Entity\JsonResponseTrait;
use Nexmo\Entity\JsonSerializableTrait;
use Nexmo\Entity\NoRequestResponseTrait;
use Nexmo\Entity\JsonUnserializableInterface;

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
        return $this['network_code'];
    }

    public function getName()
    {
        return $this['network_name'];
    }

    public function getOutboundSmsPrice()
    {
        if (isset($this['sms_price'])) {
            return $this['sms_price'];
        }
        return $this['price'];
    }

    public function getOutboundVoicePrice()
    {
        if (isset($this['voice_price'])) {
            return $this['voice_price'];
        }
        return $this['price'];
    }

    public function getPrefixPrice()
    {
        return $this['mt_price'];
    }

    public function getCurrency()
    {
        return $this['currency'];
    }

    public function jsonUnserialize(array $json)
    {
        $this->fromArray($json);
    }

    public function jsonSerialize()
    {
        return $this->toArray();
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
