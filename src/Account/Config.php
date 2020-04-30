<?php

namespace Nexmo\Account;

use Nexmo\Entity\Hydrator\ArrayHydrateInterface;

class Config implements \JsonSerializable, ArrayHydrateInterface
{
    /**
     * @var array<string, int|string>
     */
    protected $data;

    public function __construct(
        string $sms_callback_url = null,
        string $dr_callback_url = null,
        int $max_outbound_request = null,
        int $max_inbound_request = null,
        int $max_calls_per_second = null
    )
    {
        if (!is_null($sms_callback_url)) {
            $this->data['sms_callback_url'] = $sms_callback_url;
        }
        if (!is_null($dr_callback_url)) {
            $this->data['dr_callback_url'] = $dr_callback_url;
        }
        if (!is_null($max_outbound_request)) {
            $this->data['max_outbound_request'] = $max_outbound_request;
        }
        if (!is_null($max_inbound_request)) {
            $this->data['max_inbound_request'] = $max_inbound_request;
        }
        if (!is_null($max_calls_per_second)) {
            $this->data['max_calls_per_second'] = $max_calls_per_second;
        }
    }

    public function getSmsCallbackUrl() : string
    {
        return $this->data['sms_callback_url'];
    }

    public function getDrCallbackUrl() : string
    {
        return $this->data['dr_callback_url'];
    }

    public function getMaxOutboundRequest() : int
    {
        return $this->data['max_outbound_request'];
    }

    public function getMaxInboundRequest() : int
    {
        return $this->data['max_inbound_request'];
    }

    public function getMaxCallsPerSecond() : int
    {
        return $this->data['max_calls_per_second'];
    }

    /**
     * @param array<string, int|string> $data Incoming data to set the object
     */
    public function fromArray(array $data) : void
    {
        $this->data = [
            'sms_callback_url' => $data['sms_callback_url'],
            'dr_callback_url' => $data['dr_callback_url'],
            'max_outbound_request' => $data['max_outbound_request'],
            'max_inbound_request' => $data['max_inbound_request'],
            'max_calls_per_second' => $data['max_calls_per_second'],
        ];
    }

    /**
     * @return array<string, array|scalar>
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return array<string, int|string>
     */
    public function toArray(): array
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
