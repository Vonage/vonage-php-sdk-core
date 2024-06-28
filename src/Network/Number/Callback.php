<?php

declare(strict_types=1);

namespace Vonage\Network\Number;

use BadMethodCallException;
use Vonage\Client\Callback\Callback as BaseCallback;

use function substr;

/**
 * @method null|string getType()
 * @method bool hasType()
 * @method null|string getNetwork()
 * @method bool hasNetwork()
 * @method null|string getNetworkName()
 * @method bool hasNetworkName()
 * @method null|string getValid()
 * @method bool hasValid()
 * @method null|string getPorted()
 * @method bool hasPorted()
 * @method null|string getReachable()
 * @method bool hasReachable()
 * @method null|string getRoaming()
 * @method bool hasRoaming()
 * @method null|string getRoamingCountry()
 * @method bool hasRoamingCountry()
 * @method null|string getRoamingNetwork()
 * @method bool hasRoamingNetwork()
 */
class Callback extends BaseCallback
{
    protected $expected = ['request_id', 'callback_part', 'callback_total_parts', 'number', 'status'];
    protected $optional = [
        'Type' => 'number_type',
        'Network' => 'carrier_network_code',
        'NetworkName' => 'carrier_network_name',
        'Valid' => 'valid',
        'Ported' => 'ported',
        'Reachable' => 'reachable',
        'Roaming' => 'roaming',
        'RoamingCountry' => 'roaming_country_code',
        'RoamingNetwork' => 'roaming_network_code',
    ];

    public function getId()
    {
        return $this->data['request_id'];
    }

    public function getCallbackTotal()
    {
        return $this->data['callback_total_parts'];
    }

    public function getCallbackIndex()
    {
        return $this->data['callback_part'];
    }

    public function getNumber()
    {
        return $this->data['number'];
    }

    /**
     * @param $name
     * @param $args
     */
    public function __call($name, $args)
    {
        $type = substr((string) $name, 0, 3);
        $property = substr((string) $name, 3);

        if (!isset($this->optional[$property])) {
            throw new BadMethodCallException('property does not exist: ' . $property);
        }

        $property = $this->optional[$property];
        return match ($type) {
            'get' => $this->data[$property] ?? null,
            'has' => isset($this->data[$property]),
            default => throw new BadMethodCallException('method does not exist: ' . $name),
        };
    }
}
