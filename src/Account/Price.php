<?php

namespace Nexmo\Account;

use Nexmo\Network;
use Nexmo\Entity\Hydrator\ArrayHydrateInterface;

/**
 * This class will no longer be accessible via array access, nor contain request/response information after v2.
 */
abstract class Price implements \JsonSerializable, ArrayHydrateInterface
{
    use JsonSerializableTrait;
    use NoRequestResponseTrait;
    use JsonResponseTrait;

    /**
     * @var array<string, mixed>
     */
    protected $data = [];

    public function getCountryCode()
    {
        return $this->data['country_code'];
    }

    public function getCountryDisplayName()
    {
        return $this->data['country_display_name'];
    }

    public function getCountryName()
    {
        return $this->data['country_name'];
    }

    public function getDialingPrefix()
    {
        return $this->data['dialing_prefix'];
    }

    public function getDefaultPrice()
    {
        if (isset($this->data['default_price'])) {
            return $this->data['default_price'];
        }

        if (!array_key_exists('mt', $this->data)) {
            throw new \RuntimeException('Unknown pricing for ' . $this->getCountryName() . ' (' . $this->getCountryCode() . ')');
        }
        return $this->data['mt'];
    }

    public function getCurrency()
    {
        return $this->data['currency'];
    }

    public function getNetworks()
    {
        return $this->data['networks'];
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
        trigger_error(
            get_class($this) . "::jsonUnserialize is deprecated, please fromArray() instead",
            E_USER_DEPRECATED
        );
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
}
