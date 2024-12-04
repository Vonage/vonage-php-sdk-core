<?php

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
    protected array $data = [];

    public function __construct(
        ?string $sms_callback_url = null,
        ?string $dr_callback_url = null,
        int|string|null $max_outbound_request = null,
        int|string|null $max_inbound_request = null,
        int|string|null $max_calls_per_second = null
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

    public function getMaxOutboundRequest(): int|string|null
    {
        return $this->data['max_outbound_request'];
    }

    public function getMaxInboundRequest(): int|string|null
    {
        return $this->data['max_inbound_request'];
    }

    public function getMaxCallsPerSecond(): int|string|null
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
