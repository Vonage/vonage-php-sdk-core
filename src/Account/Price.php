<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\Account;

use ArrayAccess;
use JsonSerializable;
use RuntimeException;
use Vonage\Client\Exception\Exception;
use Vonage\Entity\EntityInterface;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\JsonSerializableInterface;
use Vonage\Entity\JsonSerializableTrait;
use Vonage\Entity\JsonUnserializableInterface;
use Vonage\Entity\NoRequestResponseTrait;
use Vonage\Network;

/**
 * This class will no longer be accessible via array access, nor contain request/response information after v2.
 */
abstract class Price implements
    EntityInterface,
    JsonSerializable,
    JsonSerializableInterface,
    JsonUnserializableInterface,
    ArrayAccess,
    ArrayHydrateInterface
{
    use JsonSerializableTrait;
    use NoRequestResponseTrait;
    use JsonResponseTrait;

    /**
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * @return mixed
     */
    public function getCountryCode()
    {
        return $this->data['country_code'];
    }

    /**
     * @return mixed
     */
    public function getCountryDisplayName()
    {
        return $this->data['country_display_name'];
    }

    /**
     * @return mixed
     */
    public function getCountryName()
    {
        return $this->data['country_name'];
    }

    /**
     * @return mixed
     */
    public function getDialingPrefix()
    {
        return $this->data['dialing_prefix'];
    }

    /**
     * @return mixed
     */
    public function getDefaultPrice()
    {
        if (isset($this->data['default_price'])) {
            return $this->data['default_price'];
        }

        if (!array_key_exists('mt', $this->data)) {
            throw new RuntimeException(
                'Unknown pricing for ' . $this->getCountryName() . ' (' . $this->getCountryCode() . ')'
            );
        }

        return $this->data['mt'];
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->data['currency'];
    }

    /**
     * @return mixed
     */
    public function getNetworks()
    {
        return $this->data['networks'];
    }

    /**
     * @param $networkCode
     * @return mixed
     */
    public function getPriceForNetwork($networkCode)
    {
        $networks = $this->getNetworks();
        if (isset($networks[$networkCode])) {
            return $networks[$networkCode]->{$this->priceMethod}();
        }

        return $this->getDefaultPrice();
    }

    /**
     * @param array $json
     * @return void|null
     */
    public function jsonUnserialize(array $json): void
    {
        trigger_error(
            get_class($this) . "::jsonUnserialize is deprecated, please fromArray() instead",
            E_USER_DEPRECATED
        );

        $this->fromArray($json);
    }

    /**
     * @param array $data
     */
    public function fromArray(array $data): void
    {
        // Convert CamelCase to snake_case as that's how we use array access in every other object
        $storage = [];

        foreach ($data as $k => $v) {
            $k = strtolower(ltrim(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $k), '_'));

            // PrefixPrice fixes
            switch ($k) {
                case 'country':
                    $k = 'country_code';
                    break;
                case 'name':
                    $storage['country_display_name'] = $v;
                    $storage['country_name'] = $v;
                    break;
                case 'prefix':
                    $k = 'dialing_prefix';
                    break;
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

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        trigger_error(
            "Array access for " . get_class($this) . " is deprecated, please use getter methods",
            E_USER_DEPRECATED
        );

        return isset($this->data[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        trigger_error(
            "Array access for " . get_class($this) . " is deprecated, please use getter methods",
            E_USER_DEPRECATED
        );

        return $this->data[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws Exception
     */
    public function offsetSet($offset, $value): void
    {
        throw new Exception('Price is read only');
    }

    /**
     * @param mixed $offset
     * @throws Exception
     */
    public function offsetUnset($offset): void
    {
        throw new Exception('Price is read only');
    }

    /**
     * @param $key
     * @return array|null
     * @noinspection MagicMethodsValidityInspection
     */
    public function __get($key): ?array
    {
        if ($key === 'data') {
            trigger_error(
                "Direct access to " . get_class($this) . "::data is deprecated, please use getter to toArray() methods",
                E_USER_DEPRECATED
            );

            return $this->data;
        }

        return null;
    }
}
