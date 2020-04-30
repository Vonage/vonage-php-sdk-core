<?php

namespace Nexmo\Account;

use Nexmo\Network;
use Nexmo\Entity\Hydrator\ArrayHydrateInterface;

/**
 * This class will no longer be accessible via array access, nor contain request/response information after v2.
 */
abstract class Price implements \JsonSerializable, ArrayHydrateInterface
{
    /**
     * @var string
     */
    protected $countryCode;

    /**
     * @var string
     */
    protected $countryDisplayName;

    /**
     * @var string
     */
    protected $countryName;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var string
     */
    protected $defaultPrice;

    /**
     * @var string
     */
    protected $dialingPrefix;

    /**
     * @var float
     */
    protected $mt;

    /**
     * @var array<string|int, Network>
     */
    protected $networks;

    /**
     * @var string
     */
    protected $priceMethod;

    public function getCountryCode() : string
    {
        return $this->countryCode;
    }

    public function getCountryDisplayName() : string
    {
        return $this->countryDisplayName;
    }

    public function getCountryName() : string
    {
        return $this->countryName;
    }

    public function getDialingPrefix() : string
    {
        return $this->dialingPrefix;
    }

    public function getDefaultPrice() : float
    {
        if (isset($this->defaultPrice)) {
            return (float) $this->defaultPrice;
        }

        return $this->mt;
    }

    public function getCurrency() : string
    {
        return $this->currency;
    }

    /**
     * @return array<string|int, Network>
     */
    public function getNetworks() : array
    {
        return $this->networks;
    }

    public function getPriceForNetwork(int $networkCode) : float
    {
        $networks = $this->getNetworks();
        if (isset($networks[$networkCode])) {
            return $networks[$networkCode]->{$this->priceMethod}();
        }

        return $this->getDefaultPrice();
    }

    /**
     * @param array<string, array|string> $data Incoming data from API or serialization
     */
    public function fromArray(array $data) : void
    {
        $data = $this->convertKeyNames($data);

        $this->countryCode = $data['countryCode'] ?? null;
        $this->countryDisplayName = $data['countryDisplayName'] ?? null;
        $this->countryName = $data['countryName'] ?? null;
        $this->currency = $data['currency'] ?? null;
        $this->defaultPrice = $data['defaultPrice'] ?? null;
        $this->dialingPrefix = $data['dialingPrefix'] ?? null;

        // Legacy checks for old key names
        if (is_null($this->countryCode)) {
            $this->countryCode = $data['country'] ?? null;
        }
        if (is_null($this->countryName)) {
            $this->countryName = $data['name'] ?? null;
            $this->countryDisplayName = $data['name'] ?? null;
        }
        if (is_null($this->dialingPrefix)) {
            $this->dialingPrefix = $data['prefix'] ?? null;
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

        $this->networks = $networks;
    }

    /**
     * Shim to convert snake case back into the original camelCase the API uses
     * Due to legacy code, this object can take key names in as snake_case
     * instead of the camelCase format the API returns. This method allows
     * conversion from snake_case to camelCase where legacy data cannot
     * immediately be fixed.
     *
     * @param array <string, array|string> $data Data to replace key names on
     * @return array<string, array|string>
     */
    public function convertKeyNames(array $data) : array
    {
        $newData = [];
        foreach ($data as $key => $value) {
            $key = lcfirst(str_replace('_', '', ucwords($key, '_')));
            $newData[$key] = $value;
        }

        return $newData;
    }

    /**
     * @return array<string, array|string>
     */
    public function jsonSerialize() : array
    {
        return $this->toArray();
    }

    /**
     * @return array<string, array|string>
     */
    public function toArray(): array
    {
        return [
            'countryCode' => $this->getCountryCode(),
            'countryDisplayName' => $this->getCountryDisplayName(),
            'countryName' => $this->getCountryName(),
            'currency' => $this->getCurrency(),
            'defaultPrice' => (string) $this->getDefaultPrice(),
            'dialingPrefix'=> $this->getDialingPrefix(),
            'networks' => $this->getNetworks(),
            'prefix' => $this->getDialingPrefix(),
        ];
    }
}
