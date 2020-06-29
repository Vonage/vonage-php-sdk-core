<?php

namespace Nexmo;

use Nexmo\Entity\Hydrator\ArrayHydrateInterface;

class Network implements \JsonSerializable, ArrayHydrateInterface
{
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

    public function jsonSerialize()
    {
        return $this->toArray();
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
