<?php

namespace Nexmo\Account;

use ArrayAccess;
use Nexmo\Client\Exception\Exception;
use Nexmo\Network;
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
abstract class Price implements
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

    public function getCountryCode()
    {
        return $this['country_code'];
    }

    public function getCountryDisplayName()
    {
        return $this['country_display_name'];
    }

    public function getCountryName()
    {
        return $this['country_name'];
    }

    public function getDialingPrefix()
    {
        return $this['dialing_prefix'];
    }

    public function getDefaultPrice()
    {
        if (isset($this['default_price'])) {
            return $this['default_price'];
        }

        return $this['mt'];
    }

    public function getCurrency()
    {
        return $this['currency'];
    }

    public function getNetworks()
    {
        return $this['networks'];
    }

    public function getPriceForNetwork($networkCode)
    {
        $networks = $this->getNetworks();
        if (isset($networks[$networkCode])) {
            return $networks[$networkCode]->{$this->priceMethod}();
        }

        return $this->getDefaultPrice();
    }

    public function jsonUnserialize(array $json)
    {
        trigger_error('jsonUnserialize has been deprecated, please use fromArray() instead', E_USER_DEPRECATED);
        $this->fromArray($json);
    }

    public function fromArray(array $data)
    {
        // Convert CamelCase to snake_case as that's how we use array access in every other object
        $storage = [];
        foreach ($data as $k => $v) {
            $k = ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $k)), '_');

            // PrefixPrice fixes
            if ($k == 'country') {
                $k = 'country_code';
            }

            if ($k == 'name') {
                $storage['country_display_name'] = $v;
                $storage['country_name'] = $v;
            }

            if ($k == 'prefix') {
                $k = 'dialing_prefix';
            }

            $storage[$k] = $v;
        }

        // Create objects for all the nested networks too
        $networks = [];
        if (isset($data['networks'])) {
            foreach ($data['networks'] as $n) {
                if (isset($n['code'])) {
                    $n['networkCode'] = $n['code'];
                    unset($n['code']);
                }

                if (isset($n['network'])) {
                    $n['networkName'] = $n['network'];
                    unset($n['network']);
                }

                $network = new Network($n['networkCode'], $n['networkName']);
                $network->fromArray($n);
                $networks[$network->getCode()] = $network;
            }
        }

        $storage['networks'] = $networks;
        $this->data = $storage;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray(): array
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
        throw new Exception('Price is read only');
    }

    public function offsetUnset($offset)
    {
        throw new Exception('Price is read only');
    }
}
