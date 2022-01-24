<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Account;

use JsonSerializable;
use RuntimeException;
use Vonage\Entity\EntityInterface;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\JsonSerializableInterface;
use Vonage\Entity\JsonSerializableTrait;
use Vonage\Entity\JsonUnserializableInterface;
use Vonage\Entity\NoRequestResponseTrait;
use Vonage\Network;

use function array_key_exists;
use function ltrim;
use function preg_replace;
use function strtolower;

abstract class Price implements
    EntityInterface,
    ArrayHydrateInterface
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

    public function getCountryDisplayName(): ?string
    {
        return $this->data['country_display_name'];
    }

    public function getCountryName(): ?string
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
            throw new RuntimeException(
                'Unknown pricing for ' . $this->getCountryName() . ' (' . $this->getCountryCode() . ')'
            );
        }

        return $this->data['mt'];
    }

    public function getCurrency(): ?string
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

    public function toArray(): array
    {
        return $this->data;
    }
}
