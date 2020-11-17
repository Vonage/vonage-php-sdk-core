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
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Entity\JsonSerializableInterface;
use Vonage\Entity\JsonUnserializableInterface;

use function get_class;
use function is_null;
use function trigger_error;

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
     * @param string|int|null $max_outbound_request
     * @param string|int|null $max_inbound_request
     * @param string|int|null $max_calls_per_second
     */
    public function __construct(
        ?string $sms_callback_url = null,
        ?string $dr_callback_url = null,
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

    public function getSmsCallbackUrl(): ?string
    {
        return $this->data['sms_callback_url'];
    }

    public function getDrCallbackUrl(): ?string
    {
        return $this->data['dr_callback_url'];
    }

    /**
     * @return string|int|null
     */
    public function getMaxOutboundRequest()
    {
        return $this->data['max_outbound_request'];
    }

    /**
     * @return string|int|null
     */
    public function getMaxInboundRequest()
    {
        return $this->data['max_inbound_request'];
    }

    /**
     * @return string|int|null
     */
    public function getMaxCallsPerSecond()
    {
        return $this->data['max_calls_per_second'];
    }

    public function jsonUnserialize(array $json): void
    {
        trigger_error(
            get_class($this) . "::jsonUnserialize is deprecated, please fromArray() instead",
            E_USER_DEPRECATED
        );

        $this->fromArray($json);
    }

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

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function offsetExists($offset): bool
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

    /**
     * @throws ClientException
     */
    public function offsetSet($offset, $value): void
    {
        throw new ClientException('Balance is read only');
    }

    /**
     * @throws ClientException
     */
    public function offsetUnset($offset): void
    {
        throw new ClientException('Balance is read only');
    }

    /**
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
