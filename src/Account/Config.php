<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Account;

use ArrayAccess;
use JsonSerializable;
use Vonage\Client\Exception\Exception;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Entity\JsonSerializableInterface;
use Vonage\Entity\JsonUnserializableInterface;

class Config implements
    JsonSerializable,
    JsonSerializableInterface,
    JsonUnserializableInterface,
    ArrayAccess,
    ArrayHydrateInterface
{
    /**
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * Config constructor.
     *
     * @param null $sms_callback_url
     * @param null $dr_callback_url
     * @param null $max_outbound_request
     * @param null $max_inbound_request
     * @param null $max_calls_per_second
     */
    public function __construct(
        $sms_callback_url = null,
        $dr_callback_url = null,
        $max_outbound_request = null,
        $max_inbound_request = null,
        $max_calls_per_second = null
    ) {
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

    /**
     * @return mixed
     */
    public function getSmsCallbackUrl()
    {
        return $this->data['sms_callback_url'];
    }

    /**
     * @return mixed
     */
    public function getDrCallbackUrl()
    {
        return $this->data['dr_callback_url'];
    }

    /**
     * @return mixed
     */
    public function getMaxOutboundRequest()
    {
        return $this->data['max_outbound_request'];
    }

    /**
     * @return mixed
     */
    public function getMaxInboundRequest()
    {
        return $this->data['max_inbound_request'];
    }

    /**
     * @return mixed
     */
    public function getMaxCallsPerSecond()
    {
        return $this->data['max_calls_per_second'];
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
        $this->data = [
            'sms_callback_url' => $data['sms_callback_url'],
            'dr_callback_url' => $data['dr_callback_url'],
            'max_outbound_request' => $data['max_outbound_request'],
            'max_inbound_request' => $data['max_inbound_request'],
            'max_calls_per_second' => $data['max_calls_per_second'],
        ];
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
        throw new Exception('Balance is read only');
    }

    /**
     * @param mixed $offset
     * @throws Exception
     */
    public function offsetUnset($offset): void
    {
        throw new Exception('Balance is read only');
    }

    /**
     * @param $key
     * @return array
     * @noinspection MagicMethodsValidityInspection
     */
    public function __get($key)
    {
        if ($key === 'data') {
            trigger_error(
                "Direct access to " . get_class($this) . "::data is deprecated, please use getter to toArray() methods",
                E_USER_DEPRECATED
            );

            return $this->data;
        }

        return [];
    }
}
