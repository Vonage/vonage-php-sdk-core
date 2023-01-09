<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Account;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

use function is_null;

class Config implements
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

    public function toArray(): array
    {
        return $this->data;
    }
}
